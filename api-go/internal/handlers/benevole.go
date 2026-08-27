package handlers

import (
	"net/http"

	"nomorewaste/api/internal/models"

	"github.com/gin-gonic/gin"
	"gorm.io/gorm"
)

type BenevoleHandler struct {
	DB *gorm.DB
}

func NewBenevoleHandler(db *gorm.DB) *BenevoleHandler {
	return &BenevoleHandler{DB: db}
}

func (h *BenevoleHandler) List(c *gin.Context) {
	var benevoles []models.Benevole
	if err := h.DB.Find(&benevoles).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.JSON(http.StatusOK, benevoles)
}

func (h *BenevoleHandler) Get(c *gin.Context) {
	var benevole models.Benevole
	if err := h.DB.First(&benevole, c.Param("id")).Error; err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "bénévole introuvable"})
		return
	}
	c.JSON(http.StatusOK, benevole)
}

func (h *BenevoleHandler) GetByUser(c *gin.Context) {
	var benevole models.Benevole
	if err := h.DB.Where("user_id = ?", c.Param("user_id")).First(&benevole).Error; err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "aucune candidature bénévole liée à cet utilisateur"})
		return
	}
	c.JSON(http.StatusOK, benevole)
}

func (h *BenevoleHandler) Create(c *gin.Context) {
	var benevole models.Benevole
	if err := c.ShouldBindJSON(&benevole); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}
	if benevole.Statut == "" {
		benevole.Statut = "en_attente"
	}
	if err := h.DB.Create(&benevole).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.JSON(http.StatusCreated, benevole)
}

func (h *BenevoleHandler) Update(c *gin.Context) {
	var benevole models.Benevole
	if err := h.DB.First(&benevole, c.Param("id")).Error; err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "bénévole introuvable"})
		return
	}

	var input models.Benevole
	if err := c.ShouldBindJSON(&input); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}
	input.ID = benevole.ID

	if err := h.DB.Model(&benevole).Updates(input).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusOK, benevole)
}

func (h *BenevoleHandler) Delete(c *gin.Context) {
	if err := h.DB.Delete(&models.Benevole{}, c.Param("id")).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.Status(http.StatusNoContent)
}
