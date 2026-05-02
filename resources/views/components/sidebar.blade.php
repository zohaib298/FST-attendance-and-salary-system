<x-layout>

<div class="min-h-screen bg-gray-100 flex">

    <div id="overlay" onclick="toggleSidebar()"
        class="fixed inset-0 bg-black bg-opacity-40 hidden z-30 md:hidden"></div>

    <aside id="sidebar"
        class="fixed md:fixed z-40 w-64 bg-gray-900 text-white flex flex-col shadow-xl h-screen md:h-screen transform -translate-x-full md:translate-x-0 transition-transform duration-300">

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

        <nav class="flex-1 px-4 py-6 space-y-2 text-sm overflow-y-auto">

            <a href="/" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition">
                Dashboard
            </a>

            <a href="/attendance-report" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition">
                Monthly Report
            </a>

            <a href="/payroll" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition">
                Payroll
            </a>

            <a href="/employees/profiles" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition">
                Employee Profiles
            </a>

        </nav>

        <div class="p-4 border-t border-gray-800 text-xs text-gray-500 text-center">
            © {{ date('Y') }} Fire & Safety Pvt Ltd
        </div>

    </aside>

    <div class="flex-1 flex flex-col ml-0 md:ml-64 w-full">

        <div class="md:hidden bg-white shadow px-4 py-3 flex justify-end items-center">

            <button onclick="toggleSidebar()" class="flex flex-col gap-1.5">
                <span class="w-6 h-0.5 bg-gray-800"></span>
                <span class="w-6 h-0.5 bg-gray-800"></span>
                <span class="w-6 h-0.5 bg-gray-800"></span>
            </button>

        </div>

        <main class="flex-1 p-6 overflow-y-auto h-screen">
            {{ $slot }}
        </main>

    </div>

</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}
</script>

</x-layout>