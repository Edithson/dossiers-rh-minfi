<?php

namespace Database\Seeders;

use App\Models\Piece;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PieceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pieces = [
            ['name' => 'Fiche de renseignement', 'description' => null],
            ['name' => 'Acte d’intégration ou contrat', 'description' => 'Integration decision or contract'],
            ['name' => 'Avenant de contrat', 'description' => null],
            ['name' => 'Dernier certificat de prise de service', 'description' => 'Last assumption or resumption of duty certificate'],
            ['name' => 'Décrets, arrêtés ou décision de nomination', 'description' => 'Appointment decision'],
            ['name' => 'Note d’affectation', 'description' => 'Transfert decision'],
            ['name' => 'Dernier acte d’avancement', 'description' => 'Last advancement decision'],
            ['name' => 'Présence effective au poste', 'description' => 'Attestation of effective presence'],
            ['name' => 'Certificat de prise de service', 'description' => null],
            ['name' => 'Acte de reclassement', 'description' => null],
            ['name' => 'Photocopie de l’acte de mariage éventuellement', 'description' => 'Photocopy of mariage certificate where applicable'],
            ['name' => 'Photocopie du diplôme le plus élevé et d’intégration', 'description' => 'Photocopy highest diploma and integration diploma'],
            ['name' => 'Photocopie de la CNI', 'description' => 'Photocopy of National ID Card'],
            ['name' => 'Photocopie du récépissé COPPE', 'description' => 'Photocopy of the last head count census receipt'],
            ['name' => 'Photocopies des actes de naissance des enfants mineurs', 'description' => 'Photocopies of children’s birth certificates below 21'],
            ['name' => 'Photocopie du permis de conduire éventuellement', 'description' => 'Photocopy of driving licence where applicable'],
            ['name' => 'Certificat ou attestation de formation éventuellement', 'description' => 'Training Certificate where applicable'],
            ['name' => 'Photocopie de l’acte de naissance', 'description' => 'Photocopies of birth certificate'],
        ];

        foreach ($pieces as $piece) {
            // firstOrCreate permet d'éviter les doublons si tu relances le seeder plusieurs fois
            Piece::firstOrCreate(
                ['name' => $piece['name']],
                ['description' => $piece['description']]
            );
        }
    }
}
