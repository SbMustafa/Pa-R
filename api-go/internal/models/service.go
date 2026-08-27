package models

import "time"

// Service est une prestation proposée par l'association à ses adhérents
// (conseils anti-gaspi, cours de cuisine, partage de véhicules, réparation, ...).
type Service struct {
	ID          uint      `gorm:"primaryKey" json:"id"`
	Nom         string    `gorm:"size:255;not null" json:"nom"`
	Description string    `gorm:"size:1000" json:"description"`
	Categorie   string    `gorm:"size:100" json:"categorie"`
	// Pas de tag `default` : GORM ignorerait alors la valeur false à l'insertion
	// et on ne pourrait pas créer un service directement inactif.
	Actif bool `json:"actif"`
	CreatedAt   time.Time `json:"created_at"`
	UpdatedAt   time.Time `json:"updated_at"`
}

// Seance est un créneau daté d'un service : c'est ce qui compose le planning.
// BenevoleID est le bénévole qui anime la séance (son affectation à un service).
type Seance struct {
	ID         uint      `gorm:"primaryKey" json:"id"`
	ServiceID  uint      `gorm:"index;not null" json:"service_id"`
	DateDebut  time.Time `json:"date_debut"`
	Lieu       string    `gorm:"size:255" json:"lieu"`
	PlacesMax  int       `gorm:"default:10" json:"places_max"`
	BenevoleID *uint     `gorm:"index" json:"benevole_id"`
	Statut     string    `gorm:"size:20;default:ouverte" json:"statut"`
	CreatedAt  time.Time `json:"created_at"`
	UpdatedAt  time.Time `json:"updated_at"`
}

// Inscription rattache un adhérent (compte utilisateur) à une séance.
// Le nom et l'email sont recopiés pour que les listes de participants et les
// plannings restent lisibles même si le compte est supprimé.
type Inscription struct {
	ID        uint      `gorm:"primaryKey;" json:"id"`
	SeanceID  uint      `gorm:"index:idx_seance_user,unique;not null" json:"seance_id"`
	UserID    uint      `gorm:"index:idx_seance_user,unique;not null" json:"user_id"`
	Nom       string    `gorm:"size:255" json:"nom"`
	Email     string    `gorm:"size:255" json:"email"`
	CreatedAt time.Time `json:"created_at"`
}
