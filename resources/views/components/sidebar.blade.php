<x-layout>

<div class="flex h-screen bg-gray-100 overflow-hidden">

    <!-- SIDEBAR (FIXED) -->
    <aside class="w-64 bg-gray-900 text-white flex flex-col shadow-xl fixed top-0 left-0 h-full">

        <!-- LOGO -->
        <div class="p-6 border-b border-gray-800 text-center">

            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSphh9Im04GR-pvkU2-EPtfyct5B6aAPSy9Zw&s"
                 class="w-16 h-16 mx-auto mb-3 rounded-full bg-white p-1 shadow">

            <h2 class="text-lg font-bold tracking-wide">
                Fire & Safety
            </h2>

            <p class="text-xs text-gray-400 mt-1">
                HR Management System
            </p>

        </div>

        <!-- NAV -->
        <nav class="flex-1 px-4 py-6 space-y-2 text-sm overflow-y-auto">

    <a href="/"
       class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition">
        <i class="bi bi-speedometer text-lg"></i>
        Dashboard
    </a>

    <a href="/attendance-report"
       class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition">
        <i class="bi bi-bar-chart-line text-lg"></i>
        Monthly Report
    </a>

    <a href="/payroll"
       class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition">
        <i class="bi bi-cash-stack text-lg"></i>
        Payroll
    </a>

    <a href="/employees/profiles"
       class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition">
        <i class="bi bi-person-badge text-lg"></i>
        Employee Profiles
    </a>

</nav>

        <!-- FOOTER -->
        <div class="p-4 border-t border-gray-800 text-xs text-gray-500 text-center">
            © {{ date('Y') }} Fire & Safety Pvt Ltd
        </div>

    </aside>

    <!-- MAIN CONTENT (SHIFTED RIGHT) -->
    <main class="flex-1 ml-64 p-6 overflow-y-auto h-screen">

        {{ $slot }}

    </main>

</div>

</x-layout>