package config

import (
	"fmt"
	"log"
	"os"

	"nomorewaste/api/internal/models"

	"gorm.io/driver/mysql"
	"gorm.io/gorm"
)

func ConnectDB() *gorm.DB {
	dsn := fmt.Sprintf(
		"%s:%s@tcp(%s:%s)/%s?charset=utf8mb4&parseTime=True&loc=Local",
		os.Getenv("DB_USER"),
		os.Getenv("DB_PASSWORD"),
		os.Getenv("DB_HOST"),
		os.Getenv("DB_PORT"),
		os.Getenv("DB_NAME"),
	)

	db, err := gorm.Open(mysql.Open(dsn), &gorm.Config{})
	if err != nil {
		log.Fatalf("failed to connect to database: %v", err)
	}

	if err := db.AutoMigrate(
		&models.Commercant{},
		&models.Benevole{},
		&models.Produit{},
		&models.Collecte{},
		&models.Tournee{},
		&models.LigneTournee{},
		&models.Service{},
		&models.Seance{},
		&models.Inscription{},
	); err != nil {
		log.Fatalf("failed to run migrations: %v", err)
	}

	return db
}
