<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @OA\Get(
     *     path="/rooms",
     *     summary="Get all rooms",
     *     description="Retrieve a list of all rooms with optional filtering",
     *     operationId="getRooms",
     *     tags={"Rooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter rooms by status",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"available", "occupied", "maintenance", "reserved"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="room_type_id",
     *         in="query",
     *         description="Filter rooms by room type ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="room_id", type="integer", example=1),
     *                 @OA\Property(property="room_number", type="string", example="101"),
     *                 @OA\Property(property="room_type_id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="available"),
     *                 @OA\Property(property="floor", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(
     *                     property="room_type",
     *                     type="object",
     *                     @OA\Property(property="room_type_id", type="integer", example=1),
     *                     @OA\Property(property="type_name", type="string", example="Deluxe Room")
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
        $query = Room::with('roomType');
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by room type if provided
        if ($request->has('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }
        
        $rooms = $query->get();
        return response()->json($rooms);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @OA\Post(
     *     path="/rooms",
     *     summary="Create a new room",
     *     description="Create a new room with the provided data",
     *     operationId="storeRoom",
     *     tags={"Rooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"room_number", "room_type_id", "status", "floor"},
     *             @OA\Property(property="room_number", type="string", example="101"),
     *             @OA\Property(property="room_type_id", type="integer", example=1),
     *             @OA\Property(property="status", type="string", enum={"available", "occupied", "maintenance", "reserved"}, example="available"),
     *             @OA\Property(property="floor", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Room created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Room created successfully"),
     *             @OA\Property(property="room", type="object")
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
            'room_number' => 'required|string|max:10|unique:rooms',
            'room_type_id' => 'required|exists:room_types,room_type_id',
            'status' => 'required|in:available,occupied,maintenance,reserved',
            'floor' => 'required|integer|min:1',
        ]);

        // Check if room type exists
        RoomType::findOrFail($request->room_type_id);

        $room = Room::create($request->all());

        return response()->json([
            'message' => 'Room created successfully',
            'room' => $room,
        ], 201);
    }

    /**
     * Display the specified resource.
     * 
     * @OA\Get(
     *     path="/rooms/{id}",
     *     summary="Get a specific room",
     *     description="Get detailed information about a specific room by ID",
     *     operationId="getRoomById",
     *     tags={"Rooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the room",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="room_id", type="integer", example=1),
     *             @OA\Property(property="room_number", type="string", example="101"),
     *             @OA\Property(property="room_type_id", type="integer", example=1),
     *             @OA\Property(property="status", type="string", example="available"),
     *             @OA\Property(property="floor", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time"),
     *             @OA\Property(
     *                 property="room_type",
     *                 type="object",
     *                 @OA\Property(property="room_type_id", type="integer", example=1),
     *                 @OA\Property(property="type_name", type="string", example="Deluxe Room"),
     *                 @OA\Property(property="description", type="string", example="Spacious room with king size bed"),
     *                 @OA\Property(property="base_price", type="number", format="float", example=1500.00),
     *                 @OA\Property(property="max_occupancy", type="integer", example=2),
     *                 @OA\Property(property="amenities", type="string", example="WiFi, TV, Mini Bar")
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
     *         description="Room not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Room] 1")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $room = Room::with('roomType')->findOrFail($id);
        return response()->json($room);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @OA\Put(
     *     path="/rooms/{id}",
     *     summary="Update a room",
     *     description="Update a specific room with the provided data",
     *     operationId="updateRoom",
     *     tags={"Rooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the room",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="room_number", type="string", example="102"),
     *             @OA\Property(property="room_type_id", type="integer", example=2),
     *             @OA\Property(property="status", type="string", enum={"available", "occupied", "maintenance", "reserved"}, example="maintenance"),
     *             @OA\Property(property="floor", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Room updated successfully"),
     *             @OA\Property(property="room", type="object")
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
     *         description="Room not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Room] 1")
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
        $room = Room::findOrFail($id);

        $request->validate([
            'room_number' => 'sometimes|required|string|max:10|unique:rooms,room_number,' . $id . ',room_id',
            'room_type_id' => 'sometimes|required|exists:room_types,room_type_id',
            'status' => 'sometimes|required|in:available,occupied,maintenance,reserved',
            'floor' => 'sometimes|required|integer|min:1',
        ]);

        $room->update($request->all());

        return response()->json([
            'message' => 'Room updated successfully',
            'room' => $room,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @OA\Delete(
     *     path="/rooms/{id}",
     *     summary="Delete a room",
     *     description="Delete a specific room by ID",
     *     operationId="deleteRoom",
     *     tags={"Rooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the room",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Room deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cannot delete room",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cannot delete room because it has bookings associated with it")
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
     *         description="Room not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Room] 1")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $room = Room::findOrFail($id);
        
        // Check if there are bookings for this room
        if ($room->bookings()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete room because it has bookings associated with it',
            ], 400);
        }
        
        $room->delete();

        return response()->json([
            'message' => 'Room deleted successfully',
        ]);
    }
}
