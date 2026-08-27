package handlers

import (
	"net/http"
	"strconv"
	"time"

	"nomorewaste/api/internal/models"

	"github.com/gin-gonic/gin"
	"gorm.io/gorm"
)

type CommercantHandler struct {
	DB *gorm.DB
}

func NewCommercantHandler(db *gorm.DB) *CommercantHandler {
	return &CommercantHandler{DB: db}
}

func (h *CommercantHandler) List(c *gin.Context) {
	var commercants []models.Commercant
	if err := h.DB.Find(&commercants).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.JSON(http.StatusOK, commercants)
}

func (h *CommercantHandler) Get(c *gin.Context) {
	var commercant models.Commercant
	if err := h.DB.First(&commercant, c.Param("id")).Error; err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "commerçant introuvable"})
		return
	}
	c.JSON(http.StatusOK, commercant)
}

// ARelancer liste les commerçants actifs dont l'adhésion arrive à échéance dans
// moins de ?jours (30 par défaut) — échéance déjà dépassée incluse — et qui n'ont
// pas encore été relancés depuis leur dernière échéance.
func (h *CommercantHandler) ARelancer(c *gin.Context) {
	jours := 30
	if v := c.Query("jours"); v != "" {
		if n, err := strconv.Atoi(v); err == nil && n >= 0 {
			jours = n
		}
	}
	limite := time.Now().AddDate(0, 0, jours)

	// Un rappel déjà envoyé après l'ouverture de la fenêtre de relance
	// (échéance - jours) vaut pour tout le cycle en cours : on ne relance donc
	// qu'une fois par échéance, et à nouveau après un renouvellement (qui repousse
	// date_renouvellement d'un an, donc la fenêtre avec).
	var commercants []models.Commercant
	err := h.DB.
		Where("actif = ?", true).
		Where("date_renouvellement IS NOT NULL AND date_renouvellement <= ?", limite).
		Where("date_dernier_rappel IS NULL OR date_dernier_rappel < DATE_SUB(date_renouvellement, INTERVAL ? DAY)", jours).
		Find(&commercants).Error
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.JSON(http.StatusOK, commercants)
}

func (h *CommercantHandler) GetByUser(c *gin.Context) {
	var commercant models.Commercant
	if err := h.DB.Where("user_id = ?", c.Param("user_id")).First(&commercant).Error; err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "aucune fiche commerçant liée à cet utilisateur"})
		return
	}
	c.JSON(http.StatusOK, commercant)
}

func (h *CommercantHandler) Create(c *gin.Context) {
	var commercant models.Commercant
	if err := c.ShouldBindJSON(&commercant); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}
	if commercant.DateAdhesion.IsZero() {
		commercant.DateAdhesion = time.Now()
	}
	// La cotisation est annuelle : l'échéance découle de la date d'adhésion.
	if commercant.DateRenouvellement == nil {
		echeance := commercant.DateAdhesion.AddDate(1, 0, 0)
		commercant.DateRenouvellement = &echeance
	}
	if err := h.DB.Create(&commercant).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.JSON(http.StatusCreated, commercant)
}

func (h *CommercantHandler) Update(c *gin.Context) {
	var commercant models.Commercant
	if err := h.DB.First(&commercant, c.Param("id")).Error; err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "commerçant introuvable"})
		return
	}

	var input models.Commercant
	if err := c.ShouldBindJSON(&input); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}
	input.ID = commercant.ID

	if err := h.DB.Model(&commercant).Updates(input).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusOK, commercant)
}

func (h *CommercantHandler) Delete(c *gin.Context) {
	if err := h.DB.Delete(&models.Commercant{}, c.Param("id")).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.Status(http.StatusNoContent)
}
