<?php
require('fpdf.php');

class DOCUMENTATION extends FPDF {
    // En-tête du document
    function Header() {
        $this->SetFillColor(10, 14, 20); // Fond sombre comme ton site
        $this->Rect(0, 0, 210, 40, 'F');
        $this->SetFont('Arial', 'B', 22);
        $this->SetTextColor(212, 163, 115); // Or
        $this->Cell(0, 20, utf8_decode('CITY LIBRARY - DOSSIER TECHNIQUE'), 0, 1, 'C');
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, -5, utf8_decode('Système de Gestion de Bibliothèque Intégré (LMS)'), 0, 1, 'C');
        $this->Ln(20);
    }

    // Pied de page
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, 'Page '.$this->PageNo().'/{nb} - Rapport de Projet Final', 0, 0, 'C');
    }

    // Titre de section
    function SectionTitle($label) {
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(230, 230, 230);
        $this->SetTextColor(180, 140, 50);
        $this->Cell(0, 10, "  " . utf8_decode($label), 0, 1, 'L', true);
        $this->Ln(4);
    }

    // Corps de texte
    function SectionBody($text) {
        $this->SetFont('Arial', '', 11);
        $this->SetTextColor(50, 50, 50);
        $this->MultiCell(0, 7, utf8_decode($text));
        $this->Ln(5);
    }
}

$pdf = new DOCUMENTATION();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetMargins(15, 20, 15);

// --- INTRODUCTION ---
$pdf->SectionTitle("1. Présentation Générale");
$pdf->SectionBody("Le projet City Library est une application web dynamique permettant la gestion complète d'une bibliothèque. Développé en PHP/MySQL, le site offre deux interfaces distinctes : une interface utilisateur pour la consultation et l'emprunt, et un tableau de bord administratif pour la gestion des stocks et des flux.");



// --- GESTION DES LIVRES ---
$pdf->SectionTitle("2. Gestion des Livres & Catégories");
$pdf->SectionBody("- Catalogue Dynamique : Affichage automatique des ouvrages depuis la base de données avec gestion des couvertures (PNG).\n- Catégorisation : Organisation des livres par thématiques (Fiction, Sciences, etc.).\n- Inventaire : Suivi en temps réel des exemplaires totaux et disponibles.");

// --- UTILISATEURS ET SÉCURITÉ ---
$pdf->SectionTitle("3. Sécurité et Droits d'Accès");
$pdf->SectionBody("- Rôles : Distinction entre Lecteurs (consultation/emprunt) et Administrateurs (gestion complète).\n- Authentification : Système de connexion sécurisé avec emails automatisés (@bibliotheque.com).\n- Inscription : Formulaire dédié avec génération de fiche d'adhésion au format A4 PDF.");

// --- EMPRUNTS ET RÉSERVATIONS ---
$pdf->SectionTitle("4. Circulation des Ouvrages");
$pdf->SectionBody("- Emprunts : Processus automatisé avec calcul de la date de retour (J+14).\n- Retours : Interface administrative permettant de libérer les livres et de mettre à jour les stocks.\n- Réservations : Système de file d'attente lorsque le stock d'un ouvrage est épuisé.\n- Gestion des Retards : Calcul automatique des jours de dépassement et des amendes associées.");

// --- TABLEAU DE BORD ---
$pdf->SectionTitle("5. Statistiques et Reporting");
$pdf->SectionBody("- Dashboard : Visualisation des indicateurs clés (Total livres, Lecteurs actifs, Emprunts en cours).\n- Export de données : Fonctionnalité d'extraction de l'inventaire complet au format Excel/CSV pour un traitement externe.");

// --- CONCLUSION ---
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(10, 14, 20);
$pdf->Cell(0, 10, utf8_decode("Développé avec succès - 2026"), 0, 1, 'C');

// Sortie du PDF
$pdf->Output('D', 'Documentation_City_Library.pdf');
?>