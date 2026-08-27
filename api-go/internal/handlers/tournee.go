package handlers

import (
	"encoding/json"
	"io"
	"net/http"
	"time"

	"nomorewaste/api/internal/models"

	"github.com/gin-gonic/gin"
	"gorm.io/gorm"
)

type TourneeHandler struct {
	DB *gorm.DB
}

func NewTourneeHandler(db *gorm.DB) *TourneeHandler {
	return &TourneeHandler{DB: db}
}

func (h *TourneeHandler) List(c *gin.Context) {
	var tournees []models.Tournee
	query := h.DB.Order("date_tournee DESC")
	if statut := c.Query("statut"); statut != "" {
		query = query.Where("statut = ?", statut)
	}
	if benevoleID := c.Query("benevole_id"); benevoleID != "" {
		query = query.Where("benevole_id = ?", benevoleID)
	}
	if c.Query("a_venir") == "1" {
		query = query.Where("date_tournee >= ?", time.Now().AddDate(0, 0, -1))
	}
	if err := query.Find(&tournees).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.JSON(http.StatusOK, tournees)
}

func (h *TourneeHandler) Get(c *gin.Context) {
	var tournee models.Tournee
	if err := h.DB.First(&tournee, c.Param("id")).Error; err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "tournée introuvable"})
		return
	}
	c.JSON(http.StatusOK, tournee)
}

func (h *TourneeHandler) Create(c *gin.Context) {
	var tournee models.Tournee
	if err := c.ShouldBindJSON(&tournee); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}
	if tournee.DateTournee.IsZero() {
		tournee.DateTournee = time.Now()
	}
	if tournee.Statut == "" {
		tournee.Statut = "planifiee"
	}
	if tournee.TypeDestinataire == "" {
		tournee.TypeDestinataire = "association"
	}
	if err := h.DB.Create(&tournee).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.JSON(http.StatusCreated, tournee)
}

func (h *TourneeHandler) Update(c *gin.Context) {
	var tournee models.Tournee
	if err := h.DB.First(&tournee, c.Param("id")).Error; err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "tournée introuvable"})
		return
	}

	body, err := io.ReadAll(c.Request.Body)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	// raw indique les clés envoyées, input porte les valeurs typées : permet de
	// vider un champ, contrairement à Updates(struct) qui ignore les valeurs zéro.
	var raw map[string]interface{}
	if err := json.Unmarshal(body, &raw); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}
	var input models.Tournee
	if err := json.Unmarshal(body, &input); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	updates := map[string]interface{}{}
	if _, ok := raw["destinataire"]; ok {
		updates["destinataire"] = input.Destinataire
	}
	if _, ok := raw["type_destinataire"]; ok {
		updates["type_destinataire"] = input.TypeDestinataire
	}
	if _, ok := raw["adresse"]; ok {
		updates["adresse"] = input.Adresse
	}
	if _, ok := raw["benevole_id"]; ok {
		updates["benevole_id"] = input.BenevoleID
	}
	if _, ok := raw["date_tournee"]; ok {
		updates["date_tournee"] = input.DateTournee
	}
	if _, ok := raw["statut"]; ok {
		updates["statut"] = input.Statut
	}
	if _, ok := raw["notes"]; ok {
		updates["notes"] = input.Notes
	}

	if err := h.DB.Model(&tournee).Updates(updates).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	h.DB.First(&tournee, tournee.ID)
	c.JSON(http.StatusOK, tournee)
}

// Delete supprime la tournée et remet en stock les produits qui y étaient chargés.
func (h *TourneeHandler) Delete(c *gin.Context) {
	err := h.DB.Transaction(func(tx *gorm.DB) error {
		var lignes []models.LigneTournee
		if err := tx.Where("tournee_id = ?", c.Param("id")).Find(&lignes).Error; err != nil {
			return err
		}
		for _, ligne := range lignes {
			if err := tx.Model(&models.Produit{}).Where("id = ?", ligne.ProduitID).
				UpdateColumn("quantite", gorm.Expr("quantite + ?", ligne.Quantite)).Error; err != nil {
				return err
			}
		}
		if err := tx.Where("tournee_id = ?", c.Param("id")).Delete(&models.LigneTournee{}).Error; err != nil {
			return err
		}
		return tx.Delete(&models.Tournee{}, c.Param("id")).Error
	})
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.Status(http.StatusNoContent)
}

func (h *TourneeHandler) ListLignes(c *gin.Context) {
	var lignes []models.LigneTournee
	if err := h.DB.Where("tournee_id = ?", c.Param("id")).Order("id").Find(&lignes).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.JSON(http.StatusOK, lignes)
}

// CreateLigne charge un produit dans la tournée et le sort du stock d'autant.
func (h *TourneeHandler) CreateLigne(c *gin.Context) {
	var tournee models.Tournee
	if err := h.DB.First(&tournee, c.Param("id")).Error; err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "tournée introuvable"})
		return
	}

	var input struct {
		ProduitID uint `json:"produit_id"`
		Quantite  int  `json:"quantite"`
	}
	if err := c.ShouldBindJSON(&input); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}
	if input.Quantite <= 0 {
		c.JSON(http.StatusBadRequest, gin.H{"error": "la quantité doit être supérieure à zéro"})
		return
	}

	var ligne models.LigneTournee
	err := h.DB.Transaction(func(tx *gorm.DB) error {
		var produit models.Produit
		if err := tx.First(&produit, input.ProduitID).Error; err != nil {
			return err
		}
		if produit.Quantite < input.Quantite {
			return gorm.ErrInvalidData
		}
		if err := tx.Model(&produit).
			UpdateColumn("quantite", gorm.Expr("quantite - ?", input.Quantite)).Error; err != nil {
			return err
		}

		ligne = models.LigneTournee{
			TourneeID: tournee.ID,
			ProduitID: produit.ID,
			CodeBarre: produit.CodeBarre,
			Nom:       produit.Nom,
			Quantite:  input.Quantite,
		}
		return tx.Create(&ligne).Error
	})
	if err == gorm.ErrInvalidData {
		c.JSON(http.StatusBadRequest, gin.H{"error": "quantité insuffisante en stock"})
		return
	}
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusCreated, ligne)
}

// DeleteLigne retire un produit de la tournée et le remet en stock.
func (h *TourneeHandler) DeleteLigne(c *gin.Context) {
	err := h.DB.Transaction(func(tx *gorm.DB) error {
		var ligne models.LigneTournee
		if err := tx.Where("tournee_id = ? AND id = ?", c.Param("id"), c.Param("ligne_id")).
			First(&ligne).Error; err != nil {
			return err
		}
		if err := tx.Model(&models.Produit{}).Where("id = ?", ligne.ProduitID).
			UpdateColumn("quantite", gorm.Expr("quantite + ?", ligne.Quantite)).Error; err != nil {
			return err
		}
		return tx.Delete(&ligne).Error
	})
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.Status(http.StatusNoContent)
}
