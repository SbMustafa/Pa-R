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

type CollecteHandler struct {
	DB *gorm.DB
}

func NewCollecteHandler(db *gorm.DB) *CollecteHandler {
	return &CollecteHandler{DB: db}
}

func (h *CollecteHandler) List(c *gin.Context) {
	var collectes []models.Collecte
	query := h.DB.Order("date_collecte DESC")
	if statut := c.Query("statut"); statut != "" {
		query = query.Where("statut = ?", statut)
	}
	if benevoleID := c.Query("benevole_id"); benevoleID != "" {
		query = query.Where("benevole_id = ?", benevoleID)
	}
	if c.Query("a_venir") == "1" {
		query = query.Where("date_collecte >= ?", time.Now().AddDate(0, 0, -1))
	}
	if err := query.Find(&collectes).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.JSON(http.StatusOK, collectes)
}

func (h *CollecteHandler) Get(c *gin.Context) {
	var collecte models.Collecte
	if err := h.DB.First(&collecte, c.Param("id")).Error; err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "collecte introuvable"})
		return
	}
	c.JSON(http.StatusOK, collecte)
}

func (h *CollecteHandler) Create(c *gin.Context) {
	var collecte models.Collecte
	if err := c.ShouldBindJSON(&collecte); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}
	if collecte.DateCollecte.IsZero() {
		collecte.DateCollecte = time.Now()
	}
	if collecte.Statut == "" {
		collecte.Statut = "planifiee"
	}
	if err := h.DB.Create(&collecte).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.JSON(http.StatusCreated, collecte)
}

func (h *CollecteHandler) Update(c *gin.Context) {
	var collecte models.Collecte
	if err := h.DB.First(&collecte, c.Param("id")).Error; err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "collecte introuvable"})
		return
	}

	body, err := io.ReadAll(c.Request.Body)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	// raw indique quelles clés ont été envoyées, input porte les valeurs typées :
	// permet de vider un champ (retirer un commerçant, effacer les notes), ce que
	// Updates(struct) ne ferait pas puisqu'il ignore les valeurs zéro.
	var raw map[string]interface{}
	if err := json.Unmarshal(body, &raw); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}
	var input models.Collecte
	if err := json.Unmarshal(body, &input); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	updates := map[string]interface{}{}
	if _, ok := raw["commercant_id"]; ok {
		updates["commercant_id"] = input.CommercantID
	}
	if _, ok := raw["source_libre"]; ok {
		updates["source_libre"] = input.SourceLibre
	}
	if _, ok := raw["benevole_id"]; ok {
		updates["benevole_id"] = input.BenevoleID
	}
	if _, ok := raw["date_collecte"]; ok {
		updates["date_collecte"] = input.DateCollecte
	}
	if _, ok := raw["statut"]; ok {
		updates["statut"] = input.Statut
	}
	if _, ok := raw["notes"]; ok {
		updates["notes"] = input.Notes
	}

	if err := h.DB.Model(&collecte).Updates(updates).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	h.DB.First(&collecte, collecte.ID)
	c.JSON(http.StatusOK, collecte)
}

func (h *CollecteHandler) Delete(c *gin.Context) {
	// Les produits déjà entrés en stock sont conservés : on coupe seulement
	// le lien vers la collecte supprimée.
	if err := h.DB.Model(&models.Produit{}).
		Where("collecte_id = ?", c.Param("id")).
		Update("collecte_id", nil).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	if err := h.DB.Delete(&models.Collecte{}, c.Param("id")).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.Status(http.StatusNoContent)
}
