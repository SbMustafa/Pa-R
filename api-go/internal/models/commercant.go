package models

import "time"

// Commercant représente un commerçant adhérent à NO MORE WASTE.
type Commercant struct {
	ID                 uint       `gorm:"primaryKey" json:"id"`
	UserID             *uint      `gorm:"uniqueIndex" json:"user_id"`
	Nom                string     `gorm:"size:255;not null" json:"nom"`
	Adresse            string     `gorm:"size:255" json:"adresse"`
	Ville              string     `gorm:"size:100" json:"ville"`
	CodePostal         string     `gorm:"size:10" json:"code_postal"`
	Email              string     `gorm:"size:255" json:"email"`
	Telephone          string     `gorm:"size:20" json:"telephone"`
	Siret              string     `gorm:"size:20" json:"siret"`
	DateAdhesion       time.Time  `json:"date_adhesion"`
	DateRenouvellement *time.Time `json:"date_renouvellement"`
	DateDernierRappel  *time.Time `json:"date_dernier_rappel"`
	Actif              bool       `gorm:"default:true" json:"actif"`
	CreatedAt          time.Time  `json:"created_at"`
	UpdatedAt          time.Time  `json:"updated_at"`
}
