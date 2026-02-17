<?php
/**
 * BNGRC - Application de Suivi des Dons pour les Sinistrés
 * Point d'entrée principal
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/config/routes.php';

// Démarrer la session
session_start();

// Configuration de Flight
Flight::set('flight.views.path', __DIR__ . '/../app/views');

// Lancer l'application
Flight::start();
