package models

import "time"

// Collecte représente un ramassage : un camion part du siège, récupère des invendus
// chez un commerçant adhérent (CommercantID) ou chez un particulier (SourceLibre),
// et les ramène au siège où ils sont référencés en stock.
type Collecte struct {
	ID           uint      `gorm:"primaryKey" json:"id"`
	CommercantID *uint     `gorm:"index" json:"commercant_id"`
	SourceLibre  string    `gorm:"size:255" json:"source_libre"`
	BenevoleID   *uint     `gorm:"index" json:"benevole_id"`
	DateCollecte time.Time `json:"date_collecte"`
	Statut       string    `gorm:"size:20;default:planifiee" json:"statut"`
	Notes        string    `gorm:"size:500" json:"notes"`
	CreatedAt    time.Time `json:"created_at"`
	UpdatedAt    time.Time `json:"updated_at"`
}
