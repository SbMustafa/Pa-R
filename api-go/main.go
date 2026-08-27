package main

import (
	"log"
	"os"

	"nomorewaste/api/internal/config"
	"nomorewaste/api/internal/handlers"

	"github.com/gin-contrib/cors"
	"github.com/gin-gonic/gin"
)

func main() {
	db := config.ConnectDB()

	router := gin.Default()
	router.Use(cors.New(cors.Config{
		AllowAllOrigins: true,
		AllowMethods:    []string{"GET", "POST", "PUT", "DELETE", "OPTIONS"},
		AllowHeaders:    []string{"Origin", "Content-Type", "Accept"},
	}))

	commercantHandler := handlers.NewCommercantHandler(db)
	benevoleHandler := handlers.NewBenevoleHandler(db)
	produitHandler := handlers.NewProduitHandler(db)
	collecteHandler := handlers.NewCollecteHandler(db)
	tourneeHandler := handlers.NewTourneeHandler(db)
	serviceHandler := handlers.NewServiceHandler(db)

	api := router.Group("/api")
	{
		api.GET("/commercants", commercantHandler.List)
		api.GET("/commercants/a-relancer", commercantHandler.ARelancer)
		api.GET("/commercants/by-user/:user_id", commercantHandler.GetByUser)
		api.GET("/commercants/:id", commercantHandler.Get)
		api.POST("/commercants", commercantHandler.Create)
		api.PUT("/commercants/:id", commercantHandler.Update)
		api.DELETE("/commercants/:id", commercantHandler.Delete)

		api.GET("/benevoles", benevoleHandler.List)
		api.GET("/benevoles/by-user/:user_id", benevoleHandler.GetByUser)
		api.GET("/benevoles/:id", benevoleHandler.Get)
		api.POST("/benevoles", benevoleHandler.Create)
		api.PUT("/benevoles/:id", benevoleHandler.Update)
		api.DELETE("/benevoles/:id", benevoleHandler.Delete)

		api.GET("/produits", produitHandler.List)
		api.GET("/produits/:id", produitHandler.Get)
		api.POST("/produits", produitHandler.Create)
		api.PUT("/produits/:id", produitHandler.Update)
		api.DELETE("/produits/:id", produitHandler.Delete)

		api.GET("/collectes", collecteHandler.List)
		api.GET("/collectes/:id", collecteHandler.Get)
		api.POST("/collectes", collecteHandler.Create)
		api.PUT("/collectes/:id", collecteHandler.Update)
		api.DELETE("/collectes/:id", collecteHandler.Delete)

		api.GET("/tournees", tourneeHandler.List)
		api.GET("/tournees/:id", tourneeHandler.Get)
		api.POST("/tournees", tourneeHandler.Create)
		api.PUT("/tournees/:id", tourneeHandler.Update)
		api.DELETE("/tournees/:id", tourneeHandler.Delete)
		api.GET("/tournees/:id/lignes", tourneeHandler.ListLignes)
		api.POST("/tournees/:id/lignes", tourneeHandler.CreateLigne)
		api.DELETE("/tournees/:id/lignes/:ligne_id", tourneeHandler.DeleteLigne)

		api.GET("/services", serviceHandler.List)
		api.GET("/services/:id", serviceHandler.Get)
		api.POST("/services", serviceHandler.Create)
		api.PUT("/services/:id", serviceHandler.Update)
		api.DELETE("/services/:id", serviceHandler.Delete)

		api.GET("/seances", serviceHandler.ListSeances)
		api.GET("/seances/:id", serviceHandler.GetSeance)
		api.POST("/seances", serviceHandler.CreateSeance)
		api.PUT("/seances/:id", serviceHandler.UpdateSeance)
		api.DELETE("/seances/:id", serviceHandler.DeleteSeance)
		api.GET("/seances/:id/inscriptions", serviceHandler.ListInscriptions)
		api.POST("/seances/:id/inscriptions", serviceHandler.CreateInscription)
		api.DELETE("/seances/:id/inscriptions/:inscription_id", serviceHandler.DeleteInscription)

		api.GET("/inscriptions/by-user/:user_id", serviceHandler.ListInscriptionsUtilisateur)
	}

	port := os.Getenv("PORT")
	if port == "" {
		port = "8080"
	}

	if err := router.Run(":" + port); err != nil {
		log.Fatalf("failed to start server: %v", err)
	}
}
