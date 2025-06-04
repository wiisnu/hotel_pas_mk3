<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hotel Wisnu API</title>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <!-- Styles -->
            <style>
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f8fafc;
            color: #1a202c;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        header {
            background-color: #1e40af;
            color: white;
            padding: 2rem 0;
            text-align: center;
        }
        h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        h2 {
            font-size: 1.8rem;
            margin: 2rem 0 1rem;
        }
        p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        .btn {
            display: inline-block;
            background-color: #1e40af;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            text-decoration: none;
            font-weight: 600;
            margin: 1rem 0;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #1e3a8a;
        }
        .card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .feature-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        .feature-item {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
        }
        .feature-item h3 {
            margin-top: 0;
            color: #1e40af;
        }
            </style>
    </head>
<body>
    <header>
        <div class="container">
            <h1>Hotel Wisnu API</h1>
            <p>A comprehensive RESTful API for hotel management</p>
            <a href="{{ url('api/documentation') }}" class="btn">View API Documentation</a>
        </div>
        </header>

    <div class="container">
        <div class="card">
            <h2>Welcome to Hotel Wisnu API</h2>
            <p>This API provides a complete solution for managing hotel operations including room management, bookings, services, and customer management.</p>
            <p>Use the interactive API documentation to explore and test the available endpoints.</p>
                </div>

        <h2>Key Features</h2>
        <div class="feature-list">
            <div class="feature-item">
                <h3>Authentication</h3>
                <p>Secure API access with Laravel Sanctum authentication and role-based permissions.</p>
            </div>
            <div class="feature-item">
                <h3>Room Management</h3>
                <p>Manage room types, room availability, and room status.</p>
            </div>
            <div class="feature-item">
                <h3>Booking System</h3>
                <p>Complete booking workflow with check-in/check-out functionality.</p>
            </div>
            <div class="feature-item">
                <h3>Service Management</h3>
                <p>Offer and manage additional services for guests.</p>
            </div>
            <div class="feature-item">
                <h3>Customer Management</h3>
                <p>Track customer information and booking history.</p>
            </div>
            <div class="feature-item">
                <h3>Reports & Analytics</h3>
                <p>Generate detailed reports on occupancy, revenue, and more.</p>
                </div>
        </div>

        <div class="card">
            <h2>Getting Started</h2>
            <p>To get started with the API, visit the <a href="{{ url('api/documentation') }}">API documentation</a> to learn about the available endpoints and how to use them.</p>
            <p>For authentication, use the <code>/api/register</code> and <code>/api/login</code> endpoints to obtain your API token.</p>
            <a href="{{ url('api/documentation') }}" class="btn">Explore the API</a>
        </div>
    </div>
    </body>
</html>
