package models

import "time"

// Benevole représente un bénévole de NO MORE WASTE, depuis sa candidature
// jusqu'à sa validation (l'affectation à un service viendra avec le module Services).
type Benevole struct {
	ID              uint      `gorm:"primaryKey" json:"id"`
	UserID          *uint     `gorm:"uniqueIndex" json:"user_id"`
	Nom             string    `gorm:"size:255;not null" json:"nom"`
	Email           string    `gorm:"size:255" json:"email"`
	Telephone       string    `gorm:"size:20" json:"telephone"`
	Capacites       string    `gorm:"size:255" json:"capacites"`
	Disponibilites  string    `gorm:"size:255" json:"disponibilites"`
	Statut          string    `gorm:"size:20;default:en_attente" json:"statut"`
	CreatedAt       time.Time `json:"created_at"`
	UpdatedAt       time.Time `json:"updated_at"`
}
