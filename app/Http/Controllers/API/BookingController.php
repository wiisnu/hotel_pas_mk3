<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @OA\Get(
     *     path="/bookings",
     *     summary="Get all bookings (Admin only)",
     *     description="Retrieve a list of all bookings with optional filtering",
     *     operationId="getAllBookings",
     *     tags={"Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter bookings by status",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"pending", "confirmed", "checked_in", "checked_out", "cancelled"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Filter bookings by start date",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Filter bookings by end date",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="booking_id", type="integer", example=1),
     *                 @OA\Property(property="customer_id", type="integer", example=2),
     *                 @OA\Property(property="room_id", type="integer", example=3),
     *                 @OA\Property(property="check_in_date", type="string", format="date", example="2023-07-15"),
     *                 @OA\Property(property="check_out_date", type="string", format="date", example="2023-07-20"),
     *                 @OA\Property(property="total_nights", type="integer", example=5),
     *                 @OA\Property(property="total_amount", type="number", format="float", example=750.00),
     *                 @OA\Property(property="status", type="string", example="confirmed"),
     *                 @OA\Property(property="special_requests", type="string", example="Extra pillows please"),
     *                 @OA\Property(property="booking_date", type="string", format="date", example="2023-06-10"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized.")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Booking::with(['customer', 'room.roomType']);
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('check_in_date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('check_out_date', [$request->start_date, $request->end_date]);
        }
        
        $bookings = $query->get();
        return response()->json($bookings);
    }

    /**
     * Get bookings for the authenticated customer.
     * 
     * @OA\Get(
     *     path="/my-bookings",
     *     summary="Get current customer's bookings",
     *     description="Retrieve a list of bookings for the authenticated customer",
     *     operationId="getMyBookings",
     *     tags={"Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter bookings by status",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"pending", "confirmed", "checked_in", "checked_out", "cancelled"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="booking_id", type="integer", example=1),
     *                 @OA\Property(property="customer_id", type="integer", example=2),
     *                 @OA\Property(property="room_id", type="integer", example=3),
     *                 @OA\Property(property="check_in_date", type="string", format="date", example="2023-07-15"),
     *                 @OA\Property(property="check_out_date", type="string", format="date", example="2023-07-20"),
     *                 @OA\Property(property="total_nights", type="integer", example=5),
     *                 @OA\Property(property="total_amount", type="number", format="float", example=750.00),
     *                 @OA\Property(property="status", type="string", example="confirmed"),
     *                 @OA\Property(property="special_requests", type="string", example="Extra pillows please"),
     *                 @OA\Property(property="booking_date", type="string", format="date", example="2023-06-10"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(
     *                     property="room",
     *                     type="object",
     *                     @OA\Property(property="room_id", type="integer", example=3),
     *                     @OA\Property(property="room_number", type="string", example="101"),
     *                     @OA\Property(property="room_type", type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function myBookings(Request $request)
    {
        $user = Auth::user();
        $query = Booking::with(['room.roomType'])
                        ->where('customer_id', $user->id);
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $bookings = $query->get();
        return response()->json($bookings);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @OA\Post(
     *     path="/bookings",
     *     summary="Create a new booking",
     *     description="Create a new booking with the provided data",
     *     operationId="createBooking",
     *     tags={"Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"room_id", "check_in_date", "check_out_date"},
     *             @OA\Property(property="room_id", type="integer", example=3),
     *             @OA\Property(property="check_in_date", type="string", format="date", example="2023-07-15"),
     *             @OA\Property(property="check_out_date", type="string", format="date", example="2023-07-20"),
     *             @OA\Property(property="special_requests", type="string", example="Extra pillows please")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Booking created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Booking created successfully"),
     *             @OA\Property(
     *                 property="booking",
     *                 type="object",
     *                 @OA\Property(property="booking_id", type="integer", example=1),
     *                 @OA\Property(property="customer_id", type="integer", example=2),
     *                 @OA\Property(property="room_id", type="integer", example=3),
     *                 @OA\Property(property="check_in_date", type="string", format="date", example="2023-07-15"),
     *                 @OA\Property(property="check_out_date", type="string", format="date", example="2023-07-20"),
     *                 @OA\Property(property="total_nights", type="integer", example=5),
     *                 @OA\Property(property="total_amount", type="number", format="float", example=750.00),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="special_requests", type="string", example="Extra pillows please"),
     *                 @OA\Property(property="booking_date", type="string", format="date", example="2023-06-10"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Room is not available for booking")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,room_id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'special_requests' => 'nullable|string',
        ]);

        // Check if room is available for the requested dates
        $room = Room::findOrFail($request->room_id);
        
        // Check if room is available
        if ($room->status !== 'available') {
            return response()->json([
                'message' => 'Room is not available for booking',
            ], 400);
        }
        
        // Check if room is already booked for the requested dates
        $conflictingBookings = Booking::where('room_id', $request->room_id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($request) {
                $query->whereBetween('check_in_date', [$request->check_in_date, $request->check_out_date])
                      ->orWhereBetween('check_out_date', [$request->check_in_date, $request->check_out_date])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('check_in_date', '<=', $request->check_in_date)
                            ->where('check_out_date', '>=', $request->check_out_date);
                      });
            })
            ->count();
            
        if ($conflictingBookings > 0) {
            return response()->json([
                'message' => 'Room is already booked for the requested dates',
            ], 400);
        }
        
        // Calculate total nights and amount
        $checkInDate = Carbon::parse($request->check_in_date);
        $checkOutDate = Carbon::parse($request->check_out_date);
        $totalNights = $checkInDate->diffInDays($checkOutDate);
        $totalAmount = $totalNights * $room->roomType->base_price;
        
        // Create booking
        $booking = Booking::create([
            'customer_id' => Auth::id(),
            'room_id' => $request->room_id,
            'check_in_date' => $request->check_in_date,
            'check_out_date' => $request->check_out_date,
            'total_nights' => $totalNights,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'special_requests' => $request->special_requests,
            'booking_date' => Carbon::now()->toDateString(),
        ]);
        
        // Update room status to reserved
        $room->status = 'reserved';
        $room->save();

        return response()->json([
            'message' => 'Booking created successfully',
            'booking' => $booking,
        ], 201);
    }

    /**
     * Display the specified resource.
     * 
     * @OA\Get(
     *     path="/bookings/{id}",
     *     summary="Get a specific booking",
     *     description="Get detailed information about a specific booking by ID",
     *     operationId="getBookingById",
     *     tags={"Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the booking",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="booking_id", type="integer", example=1),
     *             @OA\Property(property="customer_id", type="integer", example=2),
     *             @OA\Property(property="room_id", type="integer", example=3),
     *             @OA\Property(property="check_in_date", type="string", format="date", example="2023-07-15"),
     *             @OA\Property(property="check_out_date", type="string", format="date", example="2023-07-20"),
     *             @OA\Property(property="total_nights", type="integer", example=5),
     *             @OA\Property(property="total_amount", type="number", format="float", example=750.00),
     *             @OA\Property(property="status", type="string", example="confirmed"),
     *             @OA\Property(property="special_requests", type="string", example="Extra pillows please"),
     *             @OA\Property(property="booking_date", type="string", format="date", example="2023-06-10"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time"),
     *             @OA\Property(
     *                 property="room",
     *                 type="object",
     *                 @OA\Property(property="room_id", type="integer"),
     *                 @OA\Property(property="room_number", type="string"),
     *                 @OA\Property(property="room_type", type="object")
     *             ),
     *             @OA\Property(
     *                 property="services",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             ),
     *             @OA\Property(
     *                 property="reviews",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Booking] 1")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $booking = Booking::with(['room.roomType', 'services', 'reviews'])
                        ->findOrFail($id);
        
        // If user is customer, verify they own the booking
        if ($user->role === 'customer' && $booking->customer_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }
        
        return response()->json($booking);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @OA\Put(
     *     path="/bookings/{id}",
     *     summary="Update a booking",
     *     description="Update a specific booking with the provided data. Customers can only update special requests or cancel their bookings.",
     *     operationId="updateBooking",
     *     tags={"Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the booking",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="special_requests", type="string", example="Need a baby cot"),
     *             @OA\Property(property="status", type="string", enum={"cancelled"}, example="cancelled")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Booking updated successfully"),
     *             @OA\Property(property="booking", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cannot cancel a booking that has already been checked in or out")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Booking] 1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $booking = Booking::findOrFail($id);
        
        // If user is customer, verify they own the booking
        if ($user->role === 'customer' && $booking->customer_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }
        
        // Customers can only update special requests or cancel
        if ($user->role === 'customer') {
            $request->validate([
                'special_requests' => 'nullable|string',
                'status' => 'sometimes|in:cancelled',
            ]);
            
            // Only allow cancellation if booking is not checked in or checked out
            if ($request->has('status') && $request->status === 'cancelled') {
                if (in_array($booking->status, ['checked_in', 'checked_out'])) {
                    return response()->json([
                        'message' => 'Cannot cancel a booking that has already been checked in or out',
                    ], 400);
                }
                
                // Update room status back to available
                $room = Room::findOrFail($booking->room_id);
                $room->status = 'available';
                $room->save();
            }
            
            $booking->fill($request->only(['special_requests', 'status']));
        } else {
            // Admins can update all fields
            $request->validate([
                'check_in_date' => 'sometimes|required|date',
                'check_out_date' => 'sometimes|required|date|after:check_in_date',
                'status' => 'sometimes|required|in:pending,confirmed,checked_in,checked_out,cancelled',
                'special_requests' => 'nullable|string',
            ]);
            
            // If dates changed, recalculate total nights and amount
            if ($request->has('check_in_date') || $request->has('check_out_date')) {
                $checkInDate = $request->has('check_in_date') ? 
                    Carbon::parse($request->check_in_date) : 
                    Carbon::parse($booking->check_in_date);
                
                $checkOutDate = $request->has('check_out_date') ? 
                    Carbon::parse($request->check_out_date) : 
                    Carbon::parse($booking->check_out_date);
                
                $totalNights = $checkInDate->diffInDays($checkOutDate);
                $totalAmount = $totalNights * $booking->room->roomType->base_price;
                
                $booking->total_nights = $totalNights;
                $booking->total_amount = $totalAmount;
            }
            
            // Update room status based on booking status
            if ($request->has('status')) {
                $room = Room::findOrFail($booking->room_id);
                
                switch ($request->status) {
                    case 'pending':
                    case 'confirmed':
                        $room->status = 'reserved';
                        break;
                    case 'checked_in':
                        $room->status = 'occupied';
                        break;
                    case 'checked_out':
                    case 'cancelled':
                        $room->status = 'available';
                        break;
                }
                
                $room->save();
            }
            
            $booking->fill($request->only([
                'check_in_date', 'check_out_date', 'status', 'special_requests'
            ]));
        }
        
        $booking->save();

        return response()->json([
            'message' => 'Booking updated successfully',
            'booking' => $booking,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @OA\Delete(
     *     path="/bookings/{id}",
     *     summary="Delete a booking",
     *     description="Delete a specific booking by ID. Only pending or cancelled bookings can be deleted.",
     *     operationId="deleteBooking",
     *     tags={"Bookings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the booking",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Booking deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Only pending or cancelled bookings can be deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Booking] 1")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $booking = Booking::findOrFail($id);
        
        // If user is customer, verify they own the booking
        if ($user->role === 'customer' && $booking->customer_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }
        
        // Only allow deletion if booking is pending or cancelled
        if (!in_array($booking->status, ['pending', 'cancelled'])) {
            return response()->json([
                'message' => 'Only pending or cancelled bookings can be deleted',
            ], 400);
        }
        
        // If booking was not cancelled, update room status
        if ($booking->status !== 'cancelled') {
            $room = Room::findOrFail($booking->room_id);
            $room->status = 'available';
            $room->save();
        }
        
        $booking->delete();

        return response()->json([
            'message' => 'Booking deleted successfully',
        ]);
    }
}
