<x-layout>

<div class="flex min-h-screen">

    <aside class="w-64 bg-gray-900 text-white flex flex-col">

        
        <div class="p-6 border-b border-gray-700 text-center">

            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSphh9Im04GR-pvkU2-EPtfyct5B6aAPSy9Zw&s"
                 alt="Company Logo"
                 class="w-14 h-14 mx-auto mb-3 rounded-full bg-white p-1">

            <h2 class="text-lg font-semibold tracking-wide">
                Fire & Safety
            </h2>

            <p class="text-xs text-gray-400 mt-1">
                Pvt Ltd HR System
            </p>

        </div>

        <nav class="flex-1 px-4 py-6 space-y-1 text-sm">

            <a href="/"
               class="block px-4 py-3 rounded hover:bg-gray-800 transition">
                Employees Attendance
            </a>
            <a href="/attendance-report"
               class="block px-4 py-3 rounded hover:bg-gray-800 transition">
                Monthly Report
            </a>
            <a href="/payroll"
               class="block px-4 py-3 rounded hover:bg-gray-800 transition">
                Payroll
            </a>

            <a href="/employees/profiles"
               class="block px-4 py-3 rounded hover:bg-gray-800 transition">
                Profiles
            </a>
            

            

        </nav>

        <!-- FOOTER -->
        <div class="p-4 border-t border-gray-700 text-xs text-gray-500 text-center">
            © {{ date('Y') }} Fire & Safety Pvt Ltd
        </div>

    </aside>

    <main class="flex-1 bg-gray-100 p-6">

        {{ $slot }}

    </main>

</div>

</x-layout>