<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'booking_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'room_id',
        'check_in_date',
        'check_out_date',
        'total_nights',
        'total_amount',
        'status',
        'special_requests',
        'booking_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'total_nights' => 'integer',
        'total_amount' => 'decimal:2',
        'booking_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the customer that owns the booking.
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the room that is booked.
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    /**
     * Get the services for the booking.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'booking_services', 'booking_id', 'service_id')
            ->withPivot('quantity', 'unit_price', 'total_price', 'service_date', 'status')
            ->withTimestamps();
    }

    /**
     * Get the reviews for the booking.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'booking_id');
    }
}
