<?php

namespace Database\Seeders;

use App\Models\Tontine_plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TontinePlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
        public function run(): void
    {
        $plans = [
            ['name' => 'Tontine Simple', 'slug' => 'simple', 'default_color' => 'emerald', 'description' => 'Épargne libre et flexible au quotidien.'],
            ['name' => 'Tontine Scolaire', 'slug' => 'scolaire', 'default_color' => 'blue', 'description' => 'Préparation sereine de la rentrée des classes.'],
            ['name' => 'Tontine Investissement', 'slug' => 'investissement', 'default_color' => 'purple', 'description' => 'Fonds de roulement pour propulser vos projets.'],
            ['name' => 'Tontine Fin d\'Année', 'slug' => 'fin_annee', 'default_color' => 'amber', 'description' => 'Anticipation des fêtes et dépenses de décembre.'],
            ['name' => 'Tontine Assurance', 'slug' => 'assurance', 'default_color' => 'rose', 'description' => 'Couverture et sécurité en cas de coup dur.'],
            ['name' => 'Tontine Islamique', 'slug' => 'islamique', 'default_color' => 'teal', 'description' => 'Épargne conforme aux principes de la finance participative.'],
            ['name' => 'Tontine Marchande', 'slug' => 'marchande', 'default_color' => 'indigo', 'description' => 'Idéal pour la gestion des stocks et commerçants.'],
            ['name' => 'Tontine Électroménager', 'slug' => 'electromenager', 'default_color' => 'cyan', 'description' => 'Équipement de la maison en toute facilité.'],
        ];

        foreach ($plans ?? [] as $plan) {
            Tontine_plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }

}
