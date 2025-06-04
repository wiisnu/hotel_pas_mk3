<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Service;
use App\Models\User;
use App\Models\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    /**
     * Get booking summary report
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * 
     * @OA\Get(
     *     path="/reports/bookings",
     *     summary="Get booking summary report",
     *     description="Get summary of bookings with daily booking counts and revenue",
     *     operationId="getBookingSummary",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date for the report (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2023-01-01"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date for the report (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2023-12-31"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking summary retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_bookings", type="integer", example=120),
     *             @OA\Property(property="total_revenue", type="number", format="float", example=45000.00),
     *             @OA\Property(property="average_daily_bookings", type="number", format="float", example=4.00),
     *             @OA\Property(property="average_daily_revenue", type="number", format="float", example=1500.00),
     *             @OA\Property(property="start_date", type="string", format="date", example="2023-01-01"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2023-12-31"),
     *             @OA\Property(
     *                 property="daily_data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="date", type="string", format="date", example="2023-01-01"),
     *                     @OA\Property(property="total_bookings", type="integer", example=5),
     *                     @OA\Property(property="total_revenue", type="number", format="float", example=1500.00)
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
     *             @OA\Property(property="message", type="string", example="Unauthorized. This action requires admin privileges.")
     *         )
     *     )
     * )
     */
    public function bookingSummary(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        $bookings = Booking::whereBetween('booking_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(booking_date) as date'),
                DB::raw('COUNT(*) as total_bookings'),
                DB::raw('SUM(total_amount) as total_revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $summary = [
            'total_bookings' => $bookings->sum('total_bookings'),
            'total_revenue' => $bookings->sum('total_revenue'),
            'average_daily_bookings' => $bookings->avg('total_bookings'),
            'average_daily_revenue' => $bookings->avg('total_revenue'),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'daily_data' => $bookings,
        ];

        return response()->json($summary);
    }

    /**
     * Get room occupancy report
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * 
     * @OA\Get(
     *     path="/reports/occupancy",
     *     summary="Get room occupancy report",
     *     description="Get report on room occupancy rates over a specified period",
     *     operationId="getRoomOccupancy",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date for the report (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2023-01-01"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date for the report (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2023-12-31"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room occupancy report retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_rooms", type="integer", example=50),
     *             @OA\Property(property="average_occupancy_rate", type="number", format="float", example=65.75),
     *             @OA\Property(property="start_date", type="string", format="date", example="2023-01-01"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2023-12-31"),
     *             @OA\Property(
     *                 property="daily_data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="date", type="string", format="date", example="2023-01-01"),
     *                     @OA\Property(property="occupied_rooms", type="integer", example=32),
     *                     @OA\Property(property="occupancy_rate", type="number", format="float", example=64.00)
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
     *             @OA\Property(property="message", type="string", example="Unauthorized. This action requires admin privileges.")
     *         )
     *     )
     * )
     */
    public function roomOccupancy(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        // Get total rooms
        $totalRooms = Room::count();
        
        // Get occupied rooms per day
        $occupiedRooms = Booking::whereBetween('check_in_date', [$startDate, $endDate])
            ->orWhereBetween('check_out_date', [$startDate, $endDate])
            ->orWhere(function ($query) use ($startDate, $endDate) {
                $query->where('check_in_date', '<=', $startDate)
                      ->where('check_out_date', '>=', $endDate);
            })
            ->select(
                DB::raw('DATE(check_in_date) as date'),
                DB::raw('COUNT(DISTINCT room_id) as occupied_rooms')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Calculate occupancy rate
        $occupancyData = $occupiedRooms->map(function ($item) use ($totalRooms) {
            $item['occupancy_rate'] = $totalRooms > 0 ? round(($item['occupied_rooms'] / $totalRooms) * 100, 2) : 0;
            return $item;
        });

        $summary = [
            'total_rooms' => $totalRooms,
            'average_occupancy_rate' => $occupancyData->avg('occupancy_rate'),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'daily_data' => $occupancyData,
        ];

        return response()->json($summary);
    }

    /**
     * Get revenue by room type report
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * 
     * @OA\Get(
     *     path="/reports/revenue-by-room-type",
     *     summary="Get revenue by room type report",
     *     description="Get report on revenue breakdown by room type",
     *     operationId="getRevenueByRoomType",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date for the report (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2023-01-01"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date for the report (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2023-12-31"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Revenue by room type report retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="start_date", type="string", format="date", example="2023-01-01"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2023-12-31"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="type_name", type="string", example="Deluxe Room"),
     *                     @OA\Property(property="total_bookings", type="integer", example=45),
     *                     @OA\Property(property="total_revenue", type="number", format="float", example=22500.00)
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
     *             @OA\Property(property="message", type="string", example="Unauthorized. This action requires admin privileges.")
     *         )
     *     )
     * )
     */
    public function revenueByRoomType(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        $revenueByRoomType = Booking::join('rooms', 'bookings.room_id', '=', 'rooms.room_id')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->select(
                'room_types.type_name',
                DB::raw('COUNT(*) as total_bookings'),
                DB::raw('SUM(bookings.total_amount) as total_revenue')
            )
            ->groupBy('room_types.type_name')
            ->orderByDesc('total_revenue')
            ->get();

        $summary = [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'data' => $revenueByRoomType,
        ];

        return response()->json($summary);
    }

    /**
     * Get service usage report
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * 
     * @OA\Get(
     *     path="/reports/service-usage",
     *     summary="Get service usage report",
     *     description="Get report on service usage and revenue",
     *     operationId="getServiceUsage",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date for the report (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2023-01-01"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date for the report (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2023-12-31"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service usage report retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="start_date", type="string", format="date", example="2023-01-01"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2023-12-31"),
     *             @OA\Property(property="total_services_revenue", type="number", format="float", example=15000.00),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="service_name", type="string", example="Room Cleaning"),
     *                     @OA\Property(property="category", type="string", example="other"),
     *                     @OA\Property(property="usage_count", type="integer", example=120),
     *                     @OA\Property(property="total_quantity", type="integer", example=120),
     *                     @OA\Property(property="total_revenue", type="number", format="float", example=6000.00)
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
     *             @OA\Property(property="message", type="string", example="Unauthorized. This action requires admin privileges.")
     *         )
     *     )
     * )
     */
    public function serviceUsage(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        $serviceUsage = DB::table('booking_services')
            ->join('services', 'booking_services.service_id', '=', 'services.service_id')
            ->whereBetween('service_date', [$startDate, $endDate])
            ->select(
                'services.service_name',
                'services.category',
                DB::raw('COUNT(*) as usage_count'),
                DB::raw('SUM(booking_services.quantity) as total_quantity'),
                DB::raw('SUM(booking_services.total_price) as total_revenue')
            )
            ->groupBy('services.service_name', 'services.category')
            ->orderByDesc('total_revenue')
            ->get();

        $summary = [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_services_revenue' => $serviceUsage->sum('total_revenue'),
            'data' => $serviceUsage,
        ];

        return response()->json($summary);
    }

    /**
     * Get customer statistics report
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * 
     * @OA\Get(
     *     path="/reports/customer-statistics",
     *     summary="Get customer statistics report",
     *     description="Get report on customer statistics including top customers and new customer count",
     *     operationId="getCustomerStatistics",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date for the report (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2023-01-01"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date for the report (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2023-12-31"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer statistics report retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="start_date", type="string", format="date", example="2023-01-01"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2023-12-31"),
     *             @OA\Property(property="new_customers", type="integer", example=25),
     *             @OA\Property(
     *                 property="top_customers_by_bookings",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="full_name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                     @OA\Property(property="booking_count", type="integer", example=8),
     *                     @OA\Property(property="total_spent", type="number", format="float", example=4500.00)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="top_customers_by_revenue",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=3),
     *                     @OA\Property(property="full_name", type="string", example="Jane Smith"),
     *                     @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     *                     @OA\Property(property="booking_count", type="integer", example=5),
     *                     @OA\Property(property="total_spent", type="number", format="float", example=6000.00)
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
     *             @OA\Property(property="message", type="string", example="Unauthorized. This action requires admin privileges.")
     *         )
     *     )
     * )
     */
    public function customerStatistics(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        // Top customers by booking count
        $topCustomersByBookings = Booking::whereBetween('booking_date', [$startDate, $endDate])
            ->join('users', 'bookings.customer_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.full_name',
                'users.email',
                DB::raw('COUNT(*) as booking_count'),
                DB::raw('SUM(bookings.total_amount) as total_spent')
            )
            ->groupBy('users.id', 'users.full_name', 'users.email')
            ->orderByDesc('booking_count')
            ->limit(10)
            ->get();

        // Top customers by revenue
        $topCustomersByRevenue = Booking::whereBetween('booking_date', [$startDate, $endDate])
            ->join('users', 'bookings.customer_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.full_name',
                'users.email',
                DB::raw('COUNT(*) as booking_count'),
                DB::raw('SUM(bookings.total_amount) as total_spent')
            )
            ->groupBy('users.id', 'users.full_name', 'users.email')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get();

        // New customers in period
        $newCustomers = User::where('role', 'customer')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $summary = [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'new_customers' => $newCustomers,
            'top_customers_by_bookings' => $topCustomersByBookings,
            'top_customers_by_revenue' => $topCustomersByRevenue,
        ];

        return response()->json($summary);
    }

    /**
     * Get dashboard summary with key metrics
     * 
     * @return \Illuminate\Http\Response
     * 
     * @OA\Get(
     *     path="/reports/dashboard",
     *     summary="Get dashboard summary",
     *     description="Get summary of key metrics for the dashboard",
     *     operationId="getDashboardSummary",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard summary retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="today", type="object",
     *                 @OA\Property(property="date", type="string", format="date", example="2023-06-03"),
     *                 @OA\Property(property="bookings", type="integer", example=5),
     *                 @OA\Property(property="revenue", type="number", format="float", example=1500.00),
     *                 @OA\Property(property="check_ins", type="integer", example=3),
     *                 @OA\Property(property="check_outs", type="integer", example=2)
     *             ),
     *             @OA\Property(property="monthly", type="object",
     *                 @OA\Property(property="month", type="string", example="June 2023"),
     *                 @OA\Property(property="bookings", type="integer", example=120),
     *                 @OA\Property(property="revenue", type="number", format="float", example=45000.00)
     *             ),
     *             @OA\Property(property="rooms", type="object",
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="available", type="integer", example=20),
     *                 @OA\Property(property="occupied", type="integer", example=25),
     *                 @OA\Property(property="reserved", type="integer", example=3),
     *                 @OA\Property(property="maintenance", type="integer", example=2),
     *                 @OA\Property(property="occupancy_rate", type="number", format="float", example=56.00)
     *             ),
     *             @OA\Property(property="upcoming_bookings", type="array", @OA\Items(type="object"))
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
     *             @OA\Property(property="message", type="string", example="Unauthorized. This action requires admin privileges.")
     *         )
     *     )
     * )
     */
    public function dashboardSummary()
    {
        // Today's metrics
        $today = Carbon::today();
        $todayBookings = Booking::whereDate('booking_date', $today)->count();
        $todayRevenue = Booking::whereDate('booking_date', $today)->sum('total_amount');
        $todayCheckins = Booking::whereDate('check_in_date', $today)->count();
        $todayCheckouts = Booking::whereDate('check_out_date', $today)->count();

        // Current month metrics
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $monthlyBookings = Booking::whereBetween('booking_date', [$startOfMonth, $endOfMonth])->count();
        $monthlyRevenue = Booking::whereBetween('booking_date', [$startOfMonth, $endOfMonth])->sum('total_amount');

        // Room statistics
        $totalRooms = Room::count();
        $availableRooms = Room::where('status', 'available')->count();
        $occupiedRooms = Room::where('status', 'occupied')->count();
        $reservedRooms = Room::where('status', 'reserved')->count();
        $maintenanceRooms = Room::where('status', 'maintenance')->count();

        // Upcoming bookings
        $upcomingBookings = Booking::where('check_in_date', '>', $today)
            ->where('status', 'confirmed')
            ->orderBy('check_in_date')
            ->limit(5)
            ->with(['customer:id,full_name,email', 'room:room_id,room_number'])
            ->get();

        $summary = [
            'today' => [
                'date' => $today->toDateString(),
                'bookings' => $todayBookings,
                'revenue' => $todayRevenue,
                'check_ins' => $todayCheckins,
                'check_outs' => $todayCheckouts,
            ],
            'monthly' => [
                'month' => $today->format('F Y'),
                'bookings' => $monthlyBookings,
                'revenue' => $monthlyRevenue,
            ],
            'rooms' => [
                'total' => $totalRooms,
                'available' => $availableRooms,
                'occupied' => $occupiedRooms,
                'reserved' => $reservedRooms,
                'maintenance' => $maintenanceRooms,
                'occupancy_rate' => $totalRooms > 0 ? round((($occupiedRooms + $reservedRooms) / $totalRooms) * 100, 2) : 0,
            ],
            'upcoming_bookings' => $upcomingBookings,
        ];

        return response()->json($summary);
    }

    /**
     * Export report data in different formats
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * 
     * @OA\Get(
     *     path="/reports/export",
     *     summary="Export report data",
     *     description="Export report data in different formats (JSON, CSV)",
     *     operationId="exportReport",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="report_type",
     *         in="query",
     *         description="Type of report to export",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"bookings", "occupancy", "revenue-by-room-type", "service-usage", "customer-statistics"},
     *             example="bookings"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Export format",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"json", "csv"},
     *             example="csv"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date for the report (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2023-01-01"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date for the report (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2023-12-31"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Report data exported successfully",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         ),
     *         @OA\MediaType(
     *             mediaType="text/csv",
     *             @OA\Schema(
     *                 type="string",
     *                 format="binary"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid report type")
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
     *             @OA\Property(property="message", type="string", example="Unauthorized. This action requires admin privileges.")
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
    public function exportReport(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:bookings,occupancy,revenue-by-room-type,service-usage,customer-statistics',
            'format' => 'required|in:json,csv',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $reportType = $request->report_type;
        $format = $request->format;
        
        // Get report data based on type
        switch ($reportType) {
            case 'bookings':
                $data = $this->getBookingSummaryData($request);
                $filename = 'booking_summary_report';
                break;
            case 'occupancy':
                $data = $this->getRoomOccupancyData($request);
                $filename = 'room_occupancy_report';
                break;
            case 'revenue-by-room-type':
                $data = $this->getRevenueByRoomTypeData($request);
                $filename = 'revenue_by_room_type_report';
                break;
            case 'service-usage':
                $data = $this->getServiceUsageData($request);
                $filename = 'service_usage_report';
                break;
            case 'customer-statistics':
                $data = $this->getCustomerStatisticsData($request);
                $filename = 'customer_statistics_report';
                break;
            default:
                return response()->json(['message' => 'Invalid report type'], 400);
        }
        
        // Format and return data
        if ($format === 'json') {
            return response()->json($data);
        } else {
            // CSV export
            $output = $this->convertToCSV($data);
            return Response::make($output, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '_' . date('Y-m-d') . '.csv"',
            ]);
        }
    }

    /**
     * Get booking summary data for export
     * 
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    private function getBookingSummaryData(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        return Booking::whereBetween('booking_date', [$startDate, $endDate])
            ->join('users', 'bookings.customer_id', '=', 'users.id')
            ->join('rooms', 'bookings.room_id', '=', 'rooms.room_id')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
            ->select(
                'bookings.booking_id',
                'users.full_name as customer_name',
                'users.email as customer_email',
                'rooms.room_number',
                'room_types.type_name as room_type',
                'bookings.check_in_date',
                'bookings.check_out_date',
                'bookings.total_nights',
                'bookings.total_amount',
                'bookings.status',
                'bookings.booking_date',
                'bookings.created_at'
            )
            ->orderBy('bookings.booking_date')
            ->get()
            ->toArray();
    }

    /**
     * Get room occupancy data for export
     * 
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    private function getRoomOccupancyData(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();
        $totalRooms = Room::count();

        // Create date range
        $dates = [];
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dates[$currentDate->toDateString()] = [
                'date' => $currentDate->toDateString(),
                'occupied_rooms' => 0,
                'occupancy_rate' => 0,
            ];
            $currentDate->addDay();
        }

        // Get bookings in date range
        $bookings = Booking::whereBetween('check_in_date', [$startDate, $endDate])
            ->orWhereBetween('check_out_date', [$startDate, $endDate])
            ->orWhere(function ($query) use ($startDate, $endDate) {
                $query->where('check_in_date', '<=', $startDate)
                      ->where('check_out_date', '>=', $endDate);
            })
            ->get();

        // Calculate occupied rooms for each date
        foreach ($bookings as $booking) {
            $checkIn = Carbon::parse($booking->check_in_date);
            $checkOut = Carbon::parse($booking->check_out_date);
            
            $period = Carbon::parse($checkIn)->daysUntil($checkOut);
            foreach ($period as $date) {
                $dateString = $date->toDateString();
                if (isset($dates[$dateString])) {
                    $dates[$dateString]['occupied_rooms']++;
                }
            }
        }

        // Calculate occupancy rates
        foreach ($dates as &$date) {
            $date['occupancy_rate'] = $totalRooms > 0 ? round(($date['occupied_rooms'] / $totalRooms) * 100, 2) : 0;
        }

        return array_values($dates);
    }

    /**
     * Get revenue by room type data for export
     * 
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    private function getRevenueByRoomTypeData(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        return Booking::join('rooms', 'bookings.room_id', '=', 'rooms.room_id')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->select(
                'room_types.type_name',
                'room_types.base_price',
                DB::raw('COUNT(*) as total_bookings'),
                DB::raw('SUM(bookings.total_nights) as total_nights'),
                DB::raw('SUM(bookings.total_amount) as total_revenue'),
                DB::raw('AVG(bookings.total_amount) as average_booking_value')
            )
            ->groupBy('room_types.type_name', 'room_types.base_price')
            ->orderByDesc('total_revenue')
            ->get()
            ->toArray();
    }

    /**
     * Get service usage data for export
     * 
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    private function getServiceUsageData(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        return DB::table('booking_services')
            ->join('services', 'booking_services.service_id', '=', 'services.service_id')
            ->join('bookings', 'booking_services.booking_id', '=', 'bookings.booking_id')
            ->join('users', 'bookings.customer_id', '=', 'users.id')
            ->whereBetween('booking_services.service_date', [$startDate, $endDate])
            ->select(
                'booking_services.booking_service_id',
                'services.service_name',
                'services.category',
                'users.full_name as customer_name',
                'booking_services.quantity',
                'booking_services.unit_price',
                'booking_services.total_price',
                'booking_services.service_date',
                'booking_services.status'
            )
            ->orderBy('booking_services.service_date')
            ->get()
            ->toArray();
    }

    /**
     * Get customer statistics data for export
     * 
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    private function getCustomerStatisticsData(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        return User::where('role', 'customer')
            ->leftJoin(DB::raw("(SELECT customer_id, COUNT(*) as booking_count, SUM(total_amount) as total_spent FROM bookings WHERE booking_date BETWEEN '{$startDate->toDateString()}' AND '{$endDate->toDateString()}' GROUP BY customer_id) as booking_stats"), 'users.id', '=', 'booking_stats.customer_id')
            ->select(
                'users.id',
                'users.username',
                'users.full_name',
                'users.email',
                'users.phone',
                'users.address',
                'users.created_at',
                DB::raw('COALESCE(booking_stats.booking_count, 0) as booking_count'),
                DB::raw('COALESCE(booking_stats.total_spent, 0) as total_spent')
            )
            ->orderByDesc('booking_count')
            ->get()
            ->toArray();
    }

    /**
     * Convert data array to CSV format
     * 
     * @param array $data
     * @return string
     */
    private function convertToCSV($data)
    {
        if (empty($data)) {
            return '';
        }
        
        $output = fopen('php://temp', 'r+');
        
        // Add headers (keys from first row)
        fputcsv($output, array_keys((array)$data[0]));
        
        // Add data
        foreach ($data as $row) {
            fputcsv($output, (array)$row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    /**
     * Get yearly financial report
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * 
     * @OA\Get(
     *     path="/reports/yearly-financial",
     *     summary="Get yearly financial report",
     *     description="Get yearly financial report with monthly breakdown",
     *     operationId="getYearlyFinancialReport",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="year",
     *         in="query",
     *         description="Year for the report (YYYY)",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             example=2023
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Yearly financial report retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="year", type="integer", example=2023),
     *             @OA\Property(property="total_revenue", type="number", format="float", example=120000.00),
     *             @OA\Property(property="total_bookings", type="integer", example=450),
     *             @OA\Property(property="average_booking_value", type="number", format="float", example=266.67),
     *             @OA\Property(
     *                 property="monthly_data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="month", type="string", example="January"),
     *                     @OA\Property(property="bookings", type="integer", example=35),
     *                     @OA\Property(property="room_revenue", type="number", format="float", example=8500.00),
     *                     @OA\Property(property="service_revenue", type="number", format="float", example=1500.00),
     *                     @OA\Property(property="total_revenue", type="number", format="float", example=10000.00)
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
     *             @OA\Property(property="message", type="string", example="Unauthorized. This action requires admin privileges.")
     *         )
     *     )
     * )
     */
    public function yearlyFinancialReport(Request $request)
    {
        $request->validate([
            'year' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
        ]);

        $year = $request->year ?? date('Y');
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay();

        // Monthly booking revenue
        $monthlyRevenue = Booking::whereBetween('booking_date', [$startDate, $endDate])
            ->select(
                DB::raw('MONTH(booking_date) as month'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->toArray();

        // Monthly service revenue
        $monthlyServiceRevenue = BookingService::join('bookings', 'booking_services.booking_id', '=', 'bookings.booking_id')
            ->whereBetween('booking_services.service_date', [$startDate, $endDate])
            ->select(
                DB::raw('MONTH(booking_services.service_date) as month'),
                DB::raw('SUM(booking_services.total_price) as revenue')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->toArray();

        // Prepare monthly data with all months
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyData[$month] = [
                'month' => $month,
                'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
                'room_revenue' => $monthlyRevenue[$month]['revenue'] ?? 0,
                'service_revenue' => $monthlyServiceRevenue[$month]['revenue'] ?? 0,
                'total_revenue' => ($monthlyRevenue[$month]['revenue'] ?? 0) + ($monthlyServiceRevenue[$month]['revenue'] ?? 0),
            ];
        }

        // Room type performance
        $roomTypePerformance = Booking::join('rooms', 'bookings.room_id', '=', 'rooms.room_id')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.room_type_id')
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->select(
                'room_types.type_name',
                DB::raw('COUNT(*) as total_bookings'),
                DB::raw('SUM(bookings.total_nights) as total_nights'),
                DB::raw('SUM(bookings.total_amount) as total_revenue'),
                DB::raw('AVG(bookings.total_amount) as average_booking_value')
            )
            ->groupBy('room_types.type_name')
            ->orderByDesc('total_revenue')
            ->get();

        // Service category performance
        $serviceCategoryPerformance = BookingService::join('services', 'booking_services.service_id', '=', 'services.service_id')
            ->whereBetween('service_date', [$startDate, $endDate])
            ->select(
                'services.category',
                DB::raw('COUNT(*) as usage_count'),
                DB::raw('SUM(booking_services.total_price) as total_revenue')
            )
            ->groupBy('services.category')
            ->orderByDesc('total_revenue')
            ->get();

        // Calculate totals
        $totalRoomRevenue = array_sum(array_column($monthlyData, 'room_revenue'));
        $totalServiceRevenue = array_sum(array_column($monthlyData, 'service_revenue'));
        $totalRevenue = $totalRoomRevenue + $totalServiceRevenue;

        // Get booking counts
        $totalBookings = Booking::whereBetween('booking_date', [$startDate, $endDate])->count();
        $totalNights = Booking::whereBetween('booking_date', [$startDate, $endDate])->sum('total_nights');
        $averageBookingValue = $totalBookings > 0 ? $totalRoomRevenue / $totalBookings : 0;

        // Prepare summary
        $summary = [
            'year' => $year,
            'total_revenue' => $totalRevenue,
            'room_revenue' => $totalRoomRevenue,
            'service_revenue' => $totalServiceRevenue,
            'total_bookings' => $totalBookings,
            'total_nights' => $totalNights,
            'average_booking_value' => round($averageBookingValue, 2),
            'monthly_data' => array_values($monthlyData),
            'room_type_performance' => $roomTypePerformance,
            'service_category_performance' => $serviceCategoryPerformance,
        ];

        return response()->json($summary);
    }
}
