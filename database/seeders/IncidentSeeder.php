<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Incident;
use Illuminate\Database\Seeder;

class IncidentSeeder extends Seeder
{
   


    //Pending Incident
    public function run(): void
    {
        $reporter = User::where('role', 'reporter')->first();
        $responder = User::where('role', 'responder')->first();

       Incident::create([
        'user_id' => $reporter->id,
        'type' => 'medical',
        'severity' => 'high',
        'status' => 'pending',
        'description' => 'Person collapsed in the park.',
        'latitude' => 7.0707,
        'longitude' => 125.6087,
        'location_address' => 'Bankerohan Market, Davao City',
       ]);

       //Dispatched Incident
    Incident::create([
        'user_id' => $reporter->id,
        'responder_id' => $responder->id,
        'type' => 'fire',
        'severity' => 'high',
        'status' => 'dispatched',
        'description' => 'Small fire in a residential area.',
        'latitude' => 7.0710,
        'longitude' => 125.6090,
        'location_address' => 'Agdao, Davao City',
       ]);

       //Resolved Incident
       Incident::create([
        'user_id' => $reporter->id,
        'responder_id' => $responder->id,
        'type' => 'crime',
        'severity' => 'medium',
        'status' => 'resolved',
        'description' => 'Suspicious activity reported near the mall.',
        'latitude' => 7.0720,
        'longitude' => 125.6100,
        'location_address' => 'Ecoland, Davao City',
       ]);
    }


 }
