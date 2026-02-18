    <aside id="sidebar" class="bg-slate-900 text-white w-64 flex-shrink-0 hidden md:flex flex-col transition-transform duration-300 absolute md:relative z-20 h-full">
        <div class="p-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-wider text-blue-400">S-DAG RH</h1>
            <button id="closeSidebar" class="md:hidden text-gray-300 hover:text-white">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <nav class="flex-1 px-4 py-4 space-y-2 overflow-y-auto">
            <a href="{{route('dashboard')}}" class="flex items-center gap-3 text-gray-400 hover:bg-slate-800 hover:text-white px-4 py-3 rounded-lg transition-colors">
                <i class="fas fa-chart-pie"></i>
                <span class="font-medium">Tableau de bord</span>
            </a>
            <a href="{{route('users')}}" class="flex items-center gap-3 text-gray-400 hover:bg-slate-800 hover:text-white px-4 py-3 rounded-lg transition-colors">
                <i class="fas fa-users"></i>
                <span class="font-medium">Gestion du personnel</span>
            </a>
        </nav>
    </aside>

    <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-10 hidden md:hidden"></div>

    <script>
        //donner une classe particulière au lien actif que les autres liens n'auront pas
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('#sidebar nav a');
        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('bg-blue-600', 'text-white');
            } else {
                link.classList.remove('bg-blue-600', 'text-white');
            }
        });
    </script>
