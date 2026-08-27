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

type ServiceHandler struct {
	DB *gorm.DB
}

func NewServiceHandler(db *gorm.DB) *ServiceHandler {
	return &ServiceHandler{DB: db}
}

// --- Catalogue des services (propositions) ---

func (h *ServiceHandler) List(c *gin.Context) {
	var services []models.Service
	query := h.DB.Order("nom")
	if c.Query("actif") == "1" {
		query = query.Where("actif = ?", true)
	}
	if err := query.Find(&services).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.JSON(http.StatusOK, services)
}

func (h *ServiceHandler) Get(c *gin.Context) {
	var service models.Service
	if err := h.DB.First(&service, c.Param("id")).Error; err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "service introuvable"})
		return
	}
	c.JSON(http.StatusOK, service)
}

func (h *ServiceHandler) Create(c *gin.Context) {
	body, err := io.ReadAll(c.Request.Body)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	var raw map[string]interface{}
	if err := json.Unmarshal(body, &raw); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}
	var service models.Service
	if err := json.Unmarshal(body, &service); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}
	// Un service est actif par défaut, sauf si le champ est explicitement fourni.
	if _, ok := raw["actif"]; !ok {
		service.Actif = true
	}
	if err := h.DB.Create(&service).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.JSON(http.StatusCreated, service)
}

func (h *ServiceHandler) Update(c *gin.Context) {
	var service models.Service
	if err := h.DB.First(&service, c.Param("id")).Error; err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "service introuvable"})
		return
	}

	var input struct {
		Nom         *string `json:"nom"`
		Description *string `json:"description"`
		Categorie   *string `json:"categorie"`
		Actif       *bool   `json:"actif"`
	}
	if err := c.ShouldBindJSON(&input); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	updates := map[string]interface{}{}
	if input.Nom != nil {
		updates["nom"] = *input.Nom
	}
	if input.Description != nil {
		updates["description"] = *input.Description
	}
	if input.Categorie != nil {
		updates["categorie"] = *input.Categorie
	}
	if input.Actif != nil {
		updates["actif"] = *input.Actif
	}

	if err := h.DB.Model(&service).Updates(updates).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	h.DB.First(&service, service.ID)
	c.JSON(http.StatusOK, service)
}

// Delete supprime le service, ses séances et les inscriptions associées.
func (h *ServiceHandler) Delete(c *gin.Context) {
	err := h.DB.Transaction(func(tx *gorm.DB) error {
		var seances []models.Seance
		if err := tx.Where("service_id = ?", c.Param("id")).Find(&seances).Error; err != nil {
			return err
		}
		for _, s := range seances {
			if err := tx.Where("seance_id = ?", s.ID).Delete(&models.Inscription{}).Error; err != nil {
				return err
			}
		}
		if err := tx.Where("service_id = ?", c.Param("id")).Delete(&models.Seance{}).Error; err != nil {
			return err
		}
		return tx.Delete(&models.Service{}, c.Param("id")).Error
	})
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.Status(http.StatusNoContent)
}

// --- Séances (plannings) ---

func (h *ServiceHandler) ListSeances(c *gin.Context) {
	var seances []models.Seance
	query := h.DB.Order("date_debut")
	if serviceID := c.Query("service_id"); serviceID != "" {
		query = query.Where("service_id = ?", serviceID)
	}
	if benevoleID := c.Query("benevole_id"); benevoleID != "" {
		query = query.Where("benevole_id = ?", benevoleID)
	}
	if c.Query("a_venir") == "1" {
		query = query.Where("date_debut >= ?", time.Now())
	}
	if du := c.Query("du"); du != "" {
		query = query.Where("date_debut >= ?", du)
	}
	if au := c.Query("au"); au != "" {
		query = query.Where("date_debut <= ?", au)
	}
	if err := query.Find(&seances).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.JSON(http.StatusOK, seances)
}

func (h *ServiceHandler) GetSeance(c *gin.Context) {
	var seance models.Seance
	if err := h.DB.First(&seance, c.Param("id")).Error; err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "séance introuvable"})
		return
	}
	c.JSON(http.StatusOK, seance)
}

func (h *ServiceHandler) CreateSeance(c *gin.Context) {
	var seance models.Seance
	if err := c.ShouldBindJSON(&seance); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}
	if seance.Statut == "" {
		seance.Statut = "ouverte"
	}
	if err := h.DB.Create(&seance).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.JSON(http.StatusCreated, seance)
}

func (h *ServiceHandler) UpdateSeance(c *gin.Context) {
	var seance models.Seance
	if err := h.DB.First(&seance, c.Param("id")).Error; err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "séance introuvable"})
		return
	}

	body, err := io.ReadAll(c.Request.Body)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	// raw indique les clés envoyées, input porte les valeurs typées : permet
	// notamment de passer benevole_id à null pour désaffecter le bénévole.
	var raw map[string]interface{}
	if err := json.Unmarshal(body, &raw); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}
	var input models.Seance
	if err := json.Unmarshal(body, &input); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	updates := map[string]interface{}{}
	if _, ok := raw["service_id"]; ok {
		updates["service_id"] = input.ServiceID
	}
	if _, ok := raw["date_debut"]; ok {
		updates["date_debut"] = input.DateDebut
	}
	if _, ok := raw["lieu"]; ok {
		updates["lieu"] = input.Lieu
	}
	if _, ok := raw["places_max"]; ok {
		updates["places_max"] = input.PlacesMax
	}
	if _, ok := raw["statut"]; ok {
		updates["statut"] = input.Statut
	}
	if _, ok := raw["benevole_id"]; ok {
		updates["benevole_id"] = input.BenevoleID
	}

	if err := h.DB.Model(&seance).Updates(updates).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	h.DB.First(&seance, seance.ID)
	c.JSON(http.StatusOK, seance)
}

func (h *ServiceHandler) DeleteSeance(c *gin.Context) {
	err := h.DB.Transaction(func(tx *gorm.DB) error {
		if err := tx.Where("seance_id = ?", c.Param("id")).Delete(&models.Inscription{}).Error; err != nil {
			return err
		}
		return tx.Delete(&models.Seance{}, c.Param("id")).Error
	})
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.Status(http.StatusNoContent)
}

// --- Inscriptions ---

func (h *ServiceHandler) ListInscriptions(c *gin.Context) {
	var inscriptions []models.Inscription
	query := h.DB.Order("id")
	if seanceID := c.Param("id"); seanceID != "" {
		query = query.Where("seance_id = ?", seanceID)
	}
	if err := query.Find(&inscriptions).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.JSON(http.StatusOK, inscriptions)
}

// ListInscriptionsUtilisateur sert à afficher « mes inscriptions ».
func (h *ServiceHandler) ListInscriptionsUtilisateur(c *gin.Context) {
	var inscriptions []models.Inscription
	if err := h.DB.Where("user_id = ?", c.Param("user_id")).Order("id").
		Find(&inscriptions).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.JSON(http.StatusOK, inscriptions)
}

// CreateInscription inscrit un adhérent à une séance, en refusant les séances
// fermées, passées, complètes ou déjà réservées par cet adhérent.
func (h *ServiceHandler) CreateInscription(c *gin.Context) {
	var seance models.Seance
	if err := h.DB.First(&seance, c.Param("id")).Error; err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "séance introuvable"})
		return
	}

	var input models.Inscription
	if err := c.ShouldBindJSON(&input); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}
	input.SeanceID = seance.ID

	if seance.Statut != "ouverte" {
		c.JSON(http.StatusBadRequest, gin.H{"error": "les inscriptions à cette séance sont fermées"})
		return
	}
	if seance.DateDebut.Before(time.Now()) {
		c.JSON(http.StatusBadRequest, gin.H{"error": "cette séance est déjà passée"})
		return
	}

	var dejaInscrit int64
	h.DB.Model(&models.Inscription{}).
		Where("seance_id = ? AND user_id = ?", seance.ID, input.UserID).Count(&dejaInscrit)
	if dejaInscrit > 0 {
		c.JSON(http.StatusBadRequest, gin.H{"error": "vous êtes déjà inscrit à cette séance"})
		return
	}

	var inscrits int64
	h.DB.Model(&models.Inscription{}).Where("seance_id = ?", seance.ID).Count(&inscrits)
	if inscrits >= int64(seance.PlacesMax) {
		c.JSON(http.StatusBadRequest, gin.H{"error": "cette séance est complète"})
		return
	}

	if err := h.DB.Create(&input).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.JSON(http.StatusCreated, input)
}

func (h *ServiceHandler) DeleteInscription(c *gin.Context) {
	if err := h.DB.Where("seance_id = ? AND id = ?", c.Param("id"), c.Param("inscription_id")).
		Delete(&models.Inscription{}).Error; err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	c.Status(http.StatusNoContent)
}
