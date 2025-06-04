<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @OA\Get(
     *     path="/room-types",
     *     summary="Get all room types",
     *     description="Retrieve a list of all room types",
     *     operationId="getRoomTypes",
     *     tags={"Room Types"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="room_type_id", type="integer", example=1),
     *                 @OA\Property(property="type_name", type="string", example="Deluxe Room"),
     *                 @OA\Property(property="description", type="string", example="Spacious room with king size bed"),
     *                 @OA\Property(property="base_price", type="number", format="float", example=1500.00),
     *                 @OA\Property(property="max_occupancy", type="integer", example=2),
     *                 @OA\Property(property="amenities", type="string", example="WiFi, TV, Mini Bar"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $roomTypes = RoomType::all();
        return response()->json($roomTypes);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @OA\Post(
     *     path="/room-types",
     *     summary="Create a new room type",
     *     description="Create a new room type with the provided data",
     *     operationId="storeRoomType",
     *     tags={"Room Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type_name", "description", "base_price", "max_occupancy", "amenities"},
     *             @OA\Property(property="type_name", type="string", example="Deluxe Room"),
     *             @OA\Property(property="description", type="string", example="Spacious room with king size bed"),
     *             @OA\Property(property="base_price", type="number", format="float", example=1500.00),
     *             @OA\Property(property="max_occupancy", type="integer", example=2),
     *             @OA\Property(property="amenities", type="string", example="WiFi, TV, Mini Bar")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Room type created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Room type created successfully"),
     *             @OA\Property(property="room_type", type="object")
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
            'type_name' => 'required|string|max:255|unique:room_types',
            'description' => 'required|string',
            'base_price' => 'required|numeric|min:0',
            'max_occupancy' => 'required|integer|min:1',
            'amenities' => 'required|string',
        ]);

        $roomType = RoomType::create($request->all());

        return response()->json([
            'message' => 'Room type created successfully',
            'room_type' => $roomType,
        ], 201);
    }

    /**
     * Display the specified resource.
     * 
     * @OA\Get(
     *     path="/room-types/{id}",
     *     summary="Get a specific room type",
     *     description="Get detailed information about a specific room type by ID",
     *     operationId="getRoomTypeById",
     *     tags={"Room Types"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the room type",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="room_type_id", type="integer", example=1),
     *             @OA\Property(property="type_name", type="string", example="Deluxe Room"),
     *             @OA\Property(property="description", type="string", example="Spacious room with king size bed"),
     *             @OA\Property(property="base_price", type="number", format="float", example=1500.00),
     *             @OA\Property(property="max_occupancy", type="integer", example=2),
     *             @OA\Property(property="amenities", type="string", example="WiFi, TV, Mini Bar"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time"),
     *             @OA\Property(
     *                 property="rooms",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Room type not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\RoomType] 1")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $roomType = RoomType::with('rooms')->findOrFail($id);
        return response()->json($roomType);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @OA\Put(
     *     path="/room-types/{id}",
     *     summary="Update a room type",
     *     description="Update a specific room type with the provided data",
     *     operationId="updateRoomType",
     *     tags={"Room Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the room type",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="type_name", type="string", example="Deluxe Suite"),
     *             @OA\Property(property="description", type="string", example="Luxurious suite with king size bed and jacuzzi"),
     *             @OA\Property(property="base_price", type="number", format="float", example=2000.00),
     *             @OA\Property(property="max_occupancy", type="integer", example=3),
     *             @OA\Property(property="amenities", type="string", example="WiFi, TV, Mini Bar, Jacuzzi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room type updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Room type updated successfully"),
     *             @OA\Property(property="room_type", type="object")
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
     *         description="Room type not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\RoomType] 1")
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
        $roomType = RoomType::findOrFail($id);

        $request->validate([
            'type_name' => 'sometimes|required|string|max:255|unique:room_types,type_name,' . $id . ',room_type_id',
            'description' => 'sometimes|required|string',
            'base_price' => 'sometimes|required|numeric|min:0',
            'max_occupancy' => 'sometimes|required|integer|min:1',
            'amenities' => 'sometimes|required|string',
        ]);

        $roomType->update($request->all());

        return response()->json([
            'message' => 'Room type updated successfully',
            'room_type' => $roomType,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @OA\Delete(
     *     path="/room-types/{id}",
     *     summary="Delete a room type",
     *     description="Delete a specific room type by ID",
     *     operationId="deleteRoomType",
     *     tags={"Room Types"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the room type",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room type deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Room type deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cannot delete room type",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cannot delete room type because it has rooms associated with it")
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
     *         description="Room type not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\RoomType] 1")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $roomType = RoomType::findOrFail($id);
        
        // Check if there are rooms using this room type
        if ($roomType->rooms()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete room type because it has rooms associated with it',
            ], 400);
        }
        
        $roomType->delete();

        return response()->json([
            'message' => 'Room type deleted successfully',
        ]);
    }
}
