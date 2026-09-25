<?php
// On définit le chemin des polices AVANT d'inclure fpdf.php
define('FPDF_FONTPATH', '../font/'); 

require('fpdf.php');

// Récupération sécurisée des données
$nom = isset($_GET['nom']) ? $_GET['nom'] : "Non renseigné";
$email = isset($_GET['email']) ? $_GET['email'] : "Non renseigné";
$adresse = isset($_GET['adresse']) ? $_GET['adresse'] : "Non renseignée";
$telephone = isset($_GET['telephone']) ? $_GET['telephone'] : "Non renseigné";
$date = date('d/m/Y');

class PDF extends FPDF {
    function Header() {
        // On utilise Arial (police standard) pour éviter les bugs de fichiers externes
        $this->SetFont('Arial','B',20);
        $this->SetTextColor(212, 163, 115); // Or
        $this->Cell(0,20,'CITY LIBRARY',0,1,'C');
        
        $this->SetFont('Arial','I',12);
        $this->SetTextColor(0,0,0);
        $this->Cell(0,10,utf8_decode('Fiche d\'inscription officielle'),0,1,'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb} - City Library',0,0,'C');
    }
}

// Initialisation
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

// --- CORPS DU DOCUMENT ---
$pdf->SetFillColor(240,240,240);
$pdf->Cell(0,10,utf8_decode('Informations du Lecteur'),1,1,'L',true);
$pdf->Ln(5);

$pdf->Cell(50,10,'Nom :',0,0);
$pdf->Cell(0,10, utf8_decode($nom),0,1);

$pdf->Cell(50,10,'Email :',0,0);
$pdf->Cell(0,10, $email,0,1);

$pdf->Cell(50,10,utf8_decode('Adresse :'),0,0);
$pdf->Cell(0,10, utf8_decode($adresse),0,1);

$pdf->Cell(50,10,utf8_decode('Téléphone :'),0,0);
$pdf->Cell(0,10,utf8_decode($telephone),0,1);

$pdf->Cell(50,10,utf8_decode('Date d\'inscription :'),0,0);
$pdf->Cell(0,10, $date,0,1);

$pdf->Ln(20);
$pdf->MultiCell(0,10, utf8_decode("Règlement : L'abonné s'engage à rendre les livres dans un délai de 14 jours. Tout retard pourra entraîner des pénalités."));

$pdf->Ln(30);
$pdf->Cell(0,10,utf8_decode('Signature de l\'abonné :'),0,1,'R');
$pdf->Cell(0,30,'__________________________',0,1,'R');

// Nettoyage pour éviter les erreurs de téléchargement
if (ob_get_contents()) ob_end_clean();

$pdf->Output('D', 'Inscription_CityLibrary.pdf');
?>