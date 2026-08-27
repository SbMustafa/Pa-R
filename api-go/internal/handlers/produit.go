package handlers

import (
	"crypto/rand"
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"math/big"
	"net/http"
	"time"

	"nomorewaste/api/internal/models"

	"github.com/gin-gonic/gin"
	"gorm.io/gorm"
)

type ProduitHandler struct {
	DB *gorm.DB
}

func NewProduitHandler(db *gorm.DB) *ProduitHandler {
	return &ProduitHandler{DB: db}
}

// List retourne les produits en stock. Filtrable par code-barre (recherche rapide)
// via le paramètre de requête ?code_barre=...
func (h *ProduitHandler) List(c *gin.Context) {
	var produits []models.Produit
	query := h.DB
	// Recherche libre : code-barre (scan) ou nom du produit.
	if recherche := c.Query("q"); recherche != "" {
		motif := "%" + recherche + "%"
		query = query.Where("code_barre LIKE ? OR nom LIKE ?", motif, motif)
	}
	if codeBarre := c.Query("code_barre"); codeBarre != "" {
		query = query.Where("code_barre LIKE ?", "%"+codeBarre+"%")
	}
	if collecteID := c.Query("collecte_id"); collecteID != "" {
		query = query.Where("collecte_id = ?", collecteID)
	}
	if err := query.Find(&produits).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.JSON(http.StatusOK, produits)
}

func (h *ProduitHandler) Get(c *gin.Context) {
	var produit models.Produit
	if err := h.DB.First(&produit, c.Param("id")).Error; err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "produit introuvable"})
		return
	}
	c.JSON(http.StatusOK, produit)
}

// genererCodeBarre produit une référence interne pour les produits sans code-barre
// d'origine (invendus en vrac, dons de particuliers), que le sujet impose de
// référencer malgré tout. Format : NMW-<horodatage>-<aléa>.
func (h *ProduitHandler) genererCodeBarre() (string, error) {
	for tentative := 0; tentative < 5; tentative++ {
		n, err := rand.Int(rand.Reader, big.NewInt(10000))
		if err != nil {
			return "", err
		}
		code := fmt.Sprintf("NMW-%d-%04d", time.Now().Unix(), n.Int64())

		var count int64
		if err := h.DB.Model(&models.Produit{}).Where("code_barre = ?", code).Count(&count).Error; err != nil {
			return "", err
		}
		if count == 0 {
			return code, nil
		}
	}
	return "", errors.New("impossible de générer un code-barre unique")
}

func (h *ProduitHandler) Create(c *gin.Context) {
	var produit models.Produit
	if err := c.ShouldBindJSON(&produit); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}
	if produit.DateEntree.IsZero() {
		produit.DateEntree = time.Now()
	}
	if produit.CodeBarre == "" {
		code, err := h.genererCodeBarre()
		if err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
			return
		}
		produit.CodeBarre = code
	}
	if err := h.DB.Create(&produit).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.JSON(http.StatusCreated, produit)
}

func (h *ProduitHandler) Update(c *gin.Context) {
	var produit models.Produit
	if err := h.DB.First(&produit, c.Param("id")).Error; err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "produit introuvable"})
		return
	}

	body, err := io.ReadAll(c.Request.Body)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	// raw sert uniquement à savoir quelles clés ont été envoyées (pour que
	// quantite=0 soit appliqué, contrairement à Updates(struct) qui ignore
	// les valeurs zéro) ; input porte les valeurs correctement typées
	// (dates parsées en time.Time) pour l'update.
	var raw map[string]interface{}
	if err := json.Unmarshal(body, &raw); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}
	var input models.Produit
	if err := json.Unmarshal(body, &input); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	updates := map[string]interface{}{}
	if _, ok := raw["code_barre"]; ok {
		updates["code_barre"] = input.CodeBarre
	}
	if _, ok := raw["nom"]; ok {
		updates["nom"] = input.Nom
	}
	if _, ok := raw["quantite"]; ok {
		updates["quantite"] = input.Quantite
	}
	if _, ok := raw["emplacement"]; ok {
		updates["emplacement"] = input.Emplacement
	}
	if _, ok := raw["date_limite"]; ok {
		updates["date_limite"] = input.DateLimite
	}

	if err := h.DB.Model(&produit).Updates(updates).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	h.DB.First(&produit, produit.ID)
	c.JSON(http.StatusOK, produit)
}

func (h *ProduitHandler) Delete(c *gin.Context) {
	if err := h.DB.Delete(&models.Produit{}, c.Param("id")).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.Status(http.StatusNoContent)
}
