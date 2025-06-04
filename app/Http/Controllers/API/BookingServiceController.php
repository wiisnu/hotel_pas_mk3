<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @OA\Get(
     *     path="/booking-services",
     *     summary="Get all booking services (Admin only)",
     *     description="Retrieve a list of all booking services with optional filtering",
     *     operationId="getAllBookingServices",
     *     tags={"Booking Services"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="booking_id",
     *         in="query",
     *         description="Filter by booking ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="service_id",
     *         in="query",
     *         description="Filter by service ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"requested", "confirmed", "completed", "cancelled"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="booking_service_id", type="integer", example=1),
     *                 @OA\Property(property="booking_id", type="integer", example=1),
     *                 @OA\Property(property="service_id", type="integer", example=2),
     *                 @OA\Property(property="quantity", type="integer", example=2),
     *                 @OA\Property(property="unit_price", type="number", format="float", example=50.00),
     *                 @OA\Property(property="total_price", type="number", format="float", example=100.00),
     *                 @OA\Property(property="service_date", type="string", format="date", example="2023-07-16"),
     *                 @OA\Property(property="status", type="string", example="confirmed"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(
     *                     property="booking",
     *                     type="object"
     *                 ),
     *                 @OA\Property(
     *                     property="service",
     *                     type="object"
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
        $query = BookingService::with(['booking', 'service']);
        
        // Filter by booking ID if provided
        if ($request->has('booking_id')) {
            $query->where('booking_id', $request->booking_id);
        }
        
        // Filter by service ID if provided
        if ($request->has('service_id')) {
            $query->where('service_id', $request->service_id);
        }
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $bookingServices = $query->get();
        return response()->json($bookingServices);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @OA\Post(
     *     path="/booking-services",
     *     summary="Add a service to a booking",
     *     description="Add a service to an existing booking",
     *     operationId="addBookingService",
     *     tags={"Booking Services"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"booking_id", "service_id", "quantity", "service_date"},
     *             @OA\Property(property="booking_id", type="integer", example=1),
     *             @OA\Property(property="service_id", type="integer", example=2),
     *             @OA\Property(property="quantity", type="integer", example=2),
     *             @OA\Property(property="service_date", type="string", format="date", example="2023-07-16")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Service added to booking successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service added to booking successfully"),
     *             @OA\Property(
     *                 property="booking_service",
     *                 type="object",
     *                 @OA\Property(property="booking_service_id", type="integer", example=1),
     *                 @OA\Property(property="booking_id", type="integer", example=1),
     *                 @OA\Property(property="service_id", type="integer", example=2),
     *                 @OA\Property(property="quantity", type="integer", example=2),
     *                 @OA\Property(property="unit_price", type="number", format="float", example=50.00),
     *                 @OA\Property(property="total_price", type="number", format="float", example=100.00),
     *                 @OA\Property(property="service_date", type="string", format="date", example="2023-07-16"),
     *                 @OA\Property(property="status", type="string", example="requested"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service is not available")
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
            'booking_id' => 'required|exists:bookings,booking_id',
            'service_id' => 'required|exists:services,service_id',
            'quantity' => 'required|integer|min:1',
            'service_date' => 'required|date',
        ]);

        // Check if booking exists and belongs to the authenticated user
        $booking = Booking::findOrFail($request->booking_id);
        
        // If user is customer, verify they own the booking
        if (Auth::user()->role === 'customer' && $booking->customer_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }
        
        // Check if service exists and is active
        $service = Service::findOrFail($request->service_id);
        if (!$service->is_active) {
            return response()->json([
                'message' => 'Service is not available',
            ], 400);
        }
        
        // Calculate prices
        $unitPrice = $service->price;
        $totalPrice = $unitPrice * $request->quantity;
        
        // Create booking service
        $bookingService = BookingService::create([
            'booking_id' => $request->booking_id,
            'service_id' => $request->service_id,
            'quantity' => $request->quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'service_date' => $request->service_date,
            'status' => 'requested',
        ]);

        return response()->json([
            'message' => 'Service added to booking successfully',
            'booking_service' => $bookingService,
        ], 201);
    }

    /**
     * Display the specified resource.
     * 
     * @OA\Get(
     *     path="/booking-services/{id}",
     *     summary="Get a specific booking service",
     *     description="Get detailed information about a specific booking service by ID",
     *     operationId="getBookingServiceById",
     *     tags={"Booking Services"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the booking service",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="booking_service_id", type="integer", example=1),
     *             @OA\Property(property="booking_id", type="integer", example=1),
     *             @OA\Property(property="service_id", type="integer", example=2),
     *             @OA\Property(property="quantity", type="integer", example=2),
     *             @OA\Property(property="unit_price", type="number", format="float", example=50.00),
     *             @OA\Property(property="total_price", type="number", format="float", example=100.00),
     *             @OA\Property(property="service_date", type="string", format="date", example="2023-07-16"),
     *             @OA\Property(property="status", type="string", example="confirmed"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time"),
     *             @OA\Property(
     *                 property="booking",
     *                 type="object"
     *             ),
     *             @OA\Property(
     *                 property="service",
     *                 type="object"
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
     *         description="Booking service not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\BookingService] 1")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $bookingService = BookingService::with(['booking', 'service'])->findOrFail($id);
        
        // If user is customer, verify they own the booking
        if (Auth::user()->role === 'customer' && $bookingService->booking->customer_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }
        
        return response()->json($bookingService);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @OA\Put(
     *     path="/booking-services/{id}",
     *     summary="Update a booking service",
     *     description="Update a specific booking service with the provided data. Customers can only update quantity or cancel.",
     *     operationId="updateBookingService",
     *     tags={"Booking Services"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the booking service",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="quantity", type="integer", example=3),
     *             @OA\Property(property="status", type="string", enum={"cancelled"}, example="cancelled")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking service updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Booking service updated successfully"),
     *             @OA\Property(property="booking_service", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cannot update service that has been completed or cancelled")
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
     *         description="Booking service not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\BookingService] 1")
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
        $bookingService = BookingService::findOrFail($id);
        
        // If user is customer, verify they own the booking
        if (Auth::user()->role === 'customer' && $bookingService->booking->customer_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }
        
        // Customers can only update quantity or cancel
        if (Auth::user()->role === 'customer') {
            $request->validate([
                'quantity' => 'sometimes|required|integer|min:1',
                'status' => 'sometimes|in:cancelled',
            ]);
            
            // Only allow changes if service is not completed or cancelled
            if (!in_array($bookingService->status, ['requested', 'confirmed'])) {
                return response()->json([
                    'message' => 'Cannot update service that has been completed or cancelled',
                ], 400);
            }
            
            // Update quantity and recalculate total price
            if ($request->has('quantity')) {
                $bookingService->quantity = $request->quantity;
                $bookingService->total_price = $bookingService->unit_price * $request->quantity;
            }
            
            if ($request->has('status')) {
                $bookingService->status = $request->status;
            }
        } else {
            // Admins can update all fields
            $request->validate([
                'quantity' => 'sometimes|required|integer|min:1',
                'service_date' => 'sometimes|required|date',
                'status' => 'sometimes|required|in:requested,confirmed,completed,cancelled',
            ]);
            
            // Update quantity and recalculate total price
            if ($request->has('quantity')) {
                $bookingService->quantity = $request->quantity;
                $bookingService->total_price = $bookingService->unit_price * $request->quantity;
            }
            
            if ($request->has('service_date')) {
                $bookingService->service_date = $request->service_date;
            }
            
            if ($request->has('status')) {
                $bookingService->status = $request->status;
            }
        }
        
        $bookingService->save();

        return response()->json([
            'message' => 'Booking service updated successfully',
            'booking_service' => $bookingService,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @OA\Delete(
     *     path="/booking-services/{id}",
     *     summary="Delete a booking service",
     *     description="Delete a specific booking service by ID. Cannot delete completed services.",
     *     operationId="deleteBookingService",
     *     tags={"Booking Services"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the booking service",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking service deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Booking service deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cannot delete a service that has been completed")
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
     *         description="Booking service not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\BookingService] 1")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $bookingService = BookingService::findOrFail($id);
        
        // If user is customer, verify they own the booking
        if (Auth::user()->role === 'customer' && $bookingService->booking->customer_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }
        
        // Only allow deletion if service is not completed
        if ($bookingService->status === 'completed') {
            return response()->json([
                'message' => 'Cannot delete a service that has been completed',
            ], 400);
        }
        
        $bookingService->delete();

        return response()->json([
            'message' => 'Booking service deleted successfully',
        ]);
    }
}
