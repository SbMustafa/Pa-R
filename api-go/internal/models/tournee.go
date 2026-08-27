package models

import "time"

// Tournee représente une tournée de distribution : un camion part du siège chargé
// de produits pris en stock et les livre à un destinataire (association caritative,
// particulier en détresse, ...).
type Tournee struct {
	ID               uint      `gorm:"primaryKey" json:"id"`
	Destinataire     string    `gorm:"size:255;not null" json:"destinataire"`
	TypeDestinataire string    `gorm:"size:20;default:association" json:"type_destinataire"`
	Adresse          string    `gorm:"size:255" json:"adresse"`
	BenevoleID       *uint     `gorm:"index" json:"benevole_id"`
	DateTournee      time.Time `json:"date_tournee"`
	Statut           string    `gorm:"size:20;default:planifiee" json:"statut"`
	Notes            string    `gorm:"size:500" json:"notes"`
	CreatedAt        time.Time `json:"created_at"`
	UpdatedAt        time.Time `json:"updated_at"`
}

// LigneTournee est un produit chargé dans une tournée. Le nom et le code-barre sont
// recopiés au chargement : le récapitulatif PDF d'une livraison passée doit rester
// exact même si la fiche produit est modifiée ou supprimée par la suite.
type LigneTournee struct {
	ID        uint      `gorm:"primaryKey" json:"id"`
	TourneeID uint      `gorm:"index;not null" json:"tournee_id"`
	ProduitID uint      `gorm:"index" json:"produit_id"`
	CodeBarre string    `gorm:"size:64" json:"code_barre"`
	Nom       string    `gorm:"size:255" json:"nom"`
	Quantite  int       `json:"quantite"`
	CreatedAt time.Time `json:"created_at"`
}
