package models

import "time"

// Produit représente un produit référencé en stock (code-barre), après collecte,
// en attente d'être distribué lors d'une tournée.
type Produit struct {
	ID          uint       `gorm:"primaryKey" json:"id"`
	CollecteID  *uint      `gorm:"index" json:"collecte_id"`
	CodeBarre   string     `gorm:"size:64;uniqueIndex;not null" json:"code_barre"`
	Nom         string     `gorm:"size:255;not null" json:"nom"`
	Quantite    int        `gorm:"default:0" json:"quantite"`
	Emplacement string     `gorm:"size:100" json:"emplacement"`
	DateEntree  time.Time  `json:"date_entree"`
	DateLimite  *time.Time `json:"date_limite"`
	CreatedAt   time.Time  `json:"created_at"`
	UpdatedAt   time.Time  `json:"updated_at"`
}
