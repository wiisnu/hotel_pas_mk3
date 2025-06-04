<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Hotel Wisnu API",
 *     version="1.0.0",
 *     description="API documentation for Hotel Wisnu Management System",
 *     @OA\Contact(
 *         email="admin@hotelwisnu.com",
 *         name="Hotel Wisnu API Support"
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * ),
 * @OA\Server(
 *     url="/api",
 *     description="Hotel Wisnu API Server"
 * ),
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * ),
 * @OA\Tag(
 *     name="Authentication",
 *     description="API endpoints for user authentication"
 * ),
 * @OA\Tag(
 *     name="Users",
 *     description="API endpoints for user management"
 * ),
 * @OA\Tag(
 *     name="Room Types",
 *     description="API endpoints for room type management"
 * ),
 * @OA\Tag(
 *     name="Rooms",
 *     description="API endpoints for room management"
 * ),
 * @OA\Tag(
 *     name="Bookings",
 *     description="API endpoints for booking management"
 * ),
 * @OA\Tag(
 *     name="Services",
 *     description="API endpoints for service management"
 * ),
 * @OA\Tag(
 *     name="Booking Services",
 *     description="API endpoints for booking service management"
 * ),
 * @OA\Tag(
 *     name="Reviews",
 *     description="API endpoints for review management"
 * ),
 * @OA\Tag(
 *     name="Reports",
 *     description="API endpoints for reports and analytics"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
