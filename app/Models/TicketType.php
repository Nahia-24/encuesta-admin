<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TicketFeatures;
use App\Models\TicketCharacteristic;

class TicketType extends Model
{
    use HasFactory;
    protected $fillable = [
        'event_id',
        'name',
        'price',
        'capacity',
        'features', // asegúrate de que esté aquí
    ];

    protected $casts = [
        'features' => 'array', // Asegura que 'features' se maneje como un array
    ];

    // Relación con el evento
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function features()
    {
        // Relación many-to-many con TicketFeatures
        return $this->belongsToMany(TicketFeatures::class, 'ticket_type_feature', 'ticket_type_id', 'ticket_feature_id');
    }


    public function eventAssistant()
    {
        return $this->hasMany(EventAssistant::class, 'ticket_type_id');
    }

    public function formattedPrice()
    {
        return number_format($this->price, 0, ',', '.');
    }
    // En tu modelo TicketType
    public function characteristics()
    {
        return $this->belongsToMany(TicketCharacteristic::class, 'characteristic_ticket_feature', 'ticket_feature_id', 'ticket_characteristic_id');
    }
}
