<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @OA\Get(
     *     path="/services",
     *     summary="Get all services",
     *     description="Retrieve a list of all active services with optional filtering",
     *     operationId="getServices",
     *     tags={"Services"},
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter services by category",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"food", "laundry", "spa", "transport", "other"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         description="Filter services by active status",
     *         required=false,
     *         @OA\Schema(
     *             type="boolean"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="service_id", type="integer", example=1),
     *                 @OA\Property(property="service_name", type="string", example="Room Cleaning"),
     *                 @OA\Property(property="description", type="string", example="Standard room cleaning service"),
     *                 @OA\Property(property="price", type="number", format="float", example=50.00),
     *                 @OA\Property(property="category", type="string", example="other"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Service::query();
        
        // Filter by category if provided
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        } else {
            // By default, only show active services for public requests
            if (!Auth::check() || Auth::user()->role !== 'admin') {
                $query->where('is_active', true);
            }
        }
        
        $services = $query->get();
        return response()->json($services);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @OA\Post(
     *     path="/services",
     *     summary="Create a new service",
     *     description="Create a new service with the provided data",
     *     operationId="storeService",
     *     tags={"Services"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"service_name", "description", "price", "category"},
     *             @OA\Property(property="service_name", type="string", example="Room Cleaning"),
     *             @OA\Property(property="description", type="string", example="Standard room cleaning service"),
     *             @OA\Property(property="price", type="number", format="float", example=50.00),
     *             @OA\Property(property="category", type="string", enum={"food", "laundry", "spa", "transport", "other"}, example="other"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Service created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service created successfully"),
     *             @OA\Property(property="service", type="object")
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
            'service_name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|in:food,laundry,spa,transport,other',
            'is_active' => 'boolean',
        ]);

        $service = Service::create($request->all());

        return response()->json([
            'message' => 'Service created successfully',
            'service' => $service,
        ], 201);
    }

    /**
     * Display the specified resource.
     * 
     * @OA\Get(
     *     path="/services/{id}",
     *     summary="Get a specific service",
     *     description="Get detailed information about a specific service by ID",
     *     operationId="getServiceById",
     *     tags={"Services"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the service",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="service_id", type="integer", example=1),
     *             @OA\Property(property="service_name", type="string", example="Room Cleaning"),
     *             @OA\Property(property="description", type="string", example="Standard room cleaning service"),
     *             @OA\Property(property="price", type="number", format="float", example=50.00),
     *             @OA\Property(property="category", type="string", example="other"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service not found")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $service = Service::findOrFail($id);
        
        // Only allow viewing inactive services for admins
        if (!$service->is_active && (!Auth::check() || Auth::user()->role !== 'admin')) {
            return response()->json([
                'message' => 'Service not found',
            ], 404);
        }
        
        return response()->json($service);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @OA\Put(
     *     path="/services/{id}",
     *     summary="Update a service",
     *     description="Update a specific service with the provided data",
     *     operationId="updateService",
     *     tags={"Services"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the service",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="service_name", type="string", example="Premium Room Cleaning"),
     *             @OA\Property(property="description", type="string", example="Premium room cleaning service with additional amenities"),
     *             @OA\Property(property="price", type="number", format="float", example=75.00),
     *             @OA\Property(property="category", type="string", enum={"food", "laundry", "spa", "transport", "other"}, example="other"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service updated successfully"),
     *             @OA\Property(property="service", type="object")
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
     *         description="Service not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Service] 1")
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
        $service = Service::findOrFail($id);

        $request->validate([
            'service_name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'category' => 'sometimes|required|in:food,laundry,spa,transport,other',
            'is_active' => 'sometimes|boolean',
        ]);

        $service->update($request->all());

        return response()->json([
            'message' => 'Service updated successfully',
            'service' => $service,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @OA\Delete(
     *     path="/services/{id}",
     *     summary="Delete a service",
     *     description="Delete a specific service by ID",
     *     operationId="deleteService",
     *     tags={"Services"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the service",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cannot delete service",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cannot delete service because it is used in bookings")
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
     *         description="Service not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Service] 1")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $service = Service::findOrFail($id);
        
        // Check if service is used in any bookings
        if ($service->bookings()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete service because it is used in bookings',
            ], 400);
        }
        
        $service->delete();

        return response()->json([
            'message' => 'Service deleted successfully',
        ]);
    }
}
