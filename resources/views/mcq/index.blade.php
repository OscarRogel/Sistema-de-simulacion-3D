<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal Global MCQ & Simulador 3D - Multi-Marca</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Three.js y OrbitControls para el visor 3D -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        appDark: '#111827',
                        appSidebar: '#0f172a',
                        appAccent: '#dc2626'
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        @media print {
            body { background: white !important; color: black !important; }
            .no-print { display: none !important; }
            #detail-modal {
                position: static !important;
                background: white !important;
                padding: 0 !important;
                display: block !important;
            }
            #detail-modal > div {
                box-shadow: none !important;
                max-width: 100% !important;
                max-height: none !important;
                border: 1px solid #cbd5e1 !important;
            }
        }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased min-h-screen flex flex-col">

    <!-- Toast Notifications -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col space-y-2 no-print"></div>

    <!-- ========================================== -->
    <!-- PANTALLA DE LOGIN (VERIFICACIÓN DE USUARIO)-->
    <!-- ========================================== -->
    <div id="login-screen" class="fixed inset-0 bg-slate-950 z-50 flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-red-700 to-red-600 p-6 text-white text-center">
                <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-white/20 shadow-inner">
                    <i class="fa-solid fa-boxes-packing text-2xl"></i>
                </div>
                <h1 class="text-xl font-black tracking-tight uppercase">Sistema Global MCQ 3D</h1>
                <p class="text-xs text-red-100 mt-1">Control de Cabida por Temporada, Marca y Simulador 3D</p>
            </div>

            <form onsubmit="handleLogin(event)" class="p-6 space-y-4">
                <div id="login-error" class="hidden bg-red-500/10 border border-red-500/40 text-red-400 text-xs p-3 rounded-lg flex items-center space-x-2">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>Usuario o contraseña incorrectos.</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Usuario</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                            <i class="fa-solid fa-user"></i>
                        </span>
                        <input type="text" id="login-username" required placeholder="Ingresa tu usuario" class="w-full pl-10 pr-4 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Contraseña</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="login-password" required placeholder="••••••••" class="w-full pl-10 pr-4 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <button type="submit" id="btn-login-submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 rounded-xl text-sm shadow-lg transition flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span>Iniciar Sesión</span>
                </button>

            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- APLICACIÓN PRINCIPAL                       -->
    <!-- ========================================== -->
    <div id="app-wrapper" class="hidden min-h-screen flex flex-col lg:flex-row no-print">

        <!-- BARRA LATERAL (MENÚ PRINCIPAL) -->
        <aside class="w-full lg:w-64 bg-appSidebar text-white flex-shrink-0 flex flex-col justify-between border-b lg:border-b-0 lg:border-r border-slate-800">
            <div>
                <!-- Brand Header -->
                <div class="h-20 px-5 flex items-center justify-between border-b border-slate-800">
                    <div class="flex items-center space-x-3">
                        <div class="bg-red-600 p-2.5 rounded-xl text-white font-black text-sm tracking-wider shadow">
                            MCQ
                        </div>
                        <div>
                            <h2 class="font-bold text-sm leading-tight">Portal Global MCQ</h2>
                            <p class="text-[11px] text-slate-400">Digital RogCla Studios</p>
                        </div>
                    </div>
                </div>

                <!-- User Info Card -->
                <div class="p-4 mx-3 mt-4 bg-slate-800/70 rounded-xl border border-slate-700/70 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div id="user-avatar" class="w-9 h-9 rounded-lg bg-red-600 flex items-center justify-center font-bold text-sm">
                            AD
                        </div>
                        <div>
                            <p id="user-display-name" class="text-xs font-bold text-white leading-tight">Administrador</p>
                            <span id="user-role-badge" class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-red-500/20 text-red-400 border border-red-500/30 mt-0.5">
                                ADMIN
                            </span>
                        </div>
                    </div>
                    <button onclick="handleLogout()" class="text-slate-400 hover:text-red-400 p-1.5 rounded-lg transition" title="Cerrar Sesión">
                        <i class="fa-solid fa-power-off"></i>
                    </button>
                </div>

                <!-- Navigation Links -->
                <nav class="p-3 space-y-1.5 mt-2">
                    <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Menú Principal</p>
                    
                    <button onclick="switchView('dashboard')" id="nav-dashboard" class="nav-btn w-full flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition bg-red-600 text-white shadow">
                        <i class="fa-solid fa-clock-rotate-left w-4 text-center text-sm"></i>
                        <span>Vista General (Recientes)</span>
                    </button>

                    <button onclick="switchView('seasons')" id="nav-seasons" class="nav-btn w-full flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800 transition">
                        <i class="fa-solid fa-calendar-days w-4 text-center text-sm"></i>
                        <span>MCQ por Temporada y Marca</span>
                    </button>

                    <button onclick="switchView('create-mcq')" id="nav-create-mcq" class="nav-btn w-full flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800 transition">
                        <i class="fa-solid fa-plus-circle w-4 text-center text-sm"></i>
                        <span>Crear / Buscar Estilo MCQ</span>
                    </button>

                    <button onclick="switchView('simulator')" id="nav-simulator" class="nav-btn w-full flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800 transition">
                        <i class="fa-solid fa-cube w-4 text-center text-sm"></i>
                        <span>Simulador 3D Piezas / Caja</span>
                    </button>

                    <div id="admin-nav-section" class="pt-3 mt-3 border-t border-slate-800">
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-amber-500/80 mb-1">Administración</p>
                        <button onclick="switchView('admin')" id="nav-admin" class="nav-btn w-full flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800 transition">
                            <i class="fa-solid fa-users-gear w-4 text-center text-sm text-amber-400"></i>
                            <span>Usuarios y Catálogos</span>
                        </button>
                    </div>
                </nav>
            </div>

            <!-- Export / Backup Footer -->
            <div class="p-4 border-t border-slate-800 space-y-2 hidden lg:block">
                <button onclick="exportToCSV()" class="w-full bg-emerald-700 hover:bg-emerald-600 text-white py-2 px-3 rounded-lg text-xs font-semibold transition flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-file-csv"></i>
                    <span>Exportar MCQ a Excel</span>
                </button>
                <div class="grid grid-cols-2 gap-2">
                    <button onclick="exportAllData()" class="bg-slate-800 hover:bg-slate-700 text-slate-300 py-1.5 px-2 rounded-lg text-[11px] font-medium border border-slate-700 flex items-center justify-center space-x-1">
                        <i class="fa-solid fa-download"></i>
                        <span>Respaldo</span>
                    </button>
                    <label class="bg-slate-800 hover:bg-slate-700 text-slate-300 py-1.5 px-2 rounded-lg text-[11px] font-medium border border-slate-700 flex items-center justify-center space-x-1 cursor-pointer">
                        <i class="fa-solid fa-upload"></i>
                        <span>Importar</span>
                        <input type="file" accept=".json" onchange="importAllData(event)" class="hidden">
                    </label>
                </div>
            </div>
        </aside>

        <!-- CONTENIDO DINÁMICO PRINCIPAL -->
        <div class="flex-grow flex flex-col min-w-0">

            <!-- Barra Superior con Buscador Global Rápido -->
            <header class="bg-white border-b border-slate-200 px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4 sticky top-0 z-20 shadow-sm">
                <div class="flex items-center space-x-3 w-full sm:w-auto">
                    <h1 id="current-view-title" class="text-lg font-extrabold text-slate-800">Vista General de MCQs Recientes</h1>
                </div>

                <!-- Buscador Rápido de Estilo Existente -->
                <div class="flex items-center space-x-3 w-full sm:w-auto">
                    <div class="relative flex-grow sm:w-80">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" id="global-style-search" oninput="handleGlobalSearchInput(this.value)" placeholder="Buscar estilo existente (#Estilo, Marca, SP26...)" class="w-full pl-9 pr-4 py-2 bg-slate-100 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                        <!-- Dropdown de resultados de búsqueda rápida -->
                        <div id="quick-search-dropdown" class="hidden absolute left-0 right-0 mt-1 bg-white rounded-xl shadow-2xl border border-slate-200 max-h-80 overflow-y-auto z-40 divide-y divide-slate-100"></div>
                    </div>

                    <button onclick="openNewMcqClean()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow transition flex items-center space-x-1.5 whitespace-nowrap">
                        <i class="fa-solid fa-plus"></i>
                        <span>Nuevo MCQ</span>
                    </button>
                </div>
            </header>

            <!-- Contenedor de Vistas -->
            <main class="p-6 flex-grow max-w-7xl w-full mx-auto space-y-6">

                <!-- ========================================== -->
                <!-- VISTA 1: DASHBOARD GENERAL (RECIENTES)     -->
                <!-- ========================================== -->
                <section id="view-dashboard" class="space-y-6">
                    <!-- KPIs -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
                            <div>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total MCQs Registrados</p>
                                <h3 id="kpi-total-mcqs" class="text-2xl font-black text-slate-900 mt-1">0</h3>
                            </div>
                            <div class="p-3.5 bg-red-50 text-red-600 rounded-xl">
                                <i class="fa-solid fa-boxes-stacked text-xl"></i>
                            </div>
                        </div>
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
                            <div>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Temporadas Activas</p>
                                <h3 id="kpi-total-seasons" class="text-2xl font-black text-slate-900 mt-1">4</h3>
                                <p class="text-[10px] text-slate-500 font-mono">SP26 &bull; FA26 &bull; SP27 &bull; FA27</p>
                            </div>
                            <div class="p-3.5 bg-blue-50 text-blue-600 rounded-xl">
                                <i class="fa-solid fa-calendar-check text-xl"></i>
                            </div>
                        </div>
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
                            <div>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Marcas Oficiales</p>
                                <h3 id="kpi-total-brands" class="text-2xl font-black text-slate-900 mt-1">4</h3>
                                <p class="text-[10px] text-slate-500">PTG &bull; TNF &bull; KÜHL &bull; e.s.</p>
                            </div>
                            <div class="p-3.5 bg-amber-50 text-amber-600 rounded-xl">
                                <i class="fa-solid fa-tags text-xl"></i>
                            </div>
                        </div>
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
                            <div>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Eficiencia Vol. Promedio</p>
                                <h3 id="kpi-avg-eff" class="text-2xl font-black text-slate-900 mt-1">0%</h3>
                            </div>
                            <div class="p-3.5 bg-emerald-50 text-emerald-600 rounded-xl">
                                <i class="fa-solid fa-chart-pie text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Dos Columnas Principales: Recientemente Creados y Recientemente Buscados -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        <!-- Panel: MCQs Creados Recientemente -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                            <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    <h3 class="font-bold text-slate-800 text-sm">MCQs Creados Recientemente</h3>
                                </div>
                                <button onclick="switchView('seasons')" class="text-xs text-red-600 hover:underline font-semibold">Ver todos &rarr;</button>
                            </div>
                            <div id="recent-created-list" class="p-4 space-y-3 divide-y divide-slate-100 flex-grow">
                                <!-- Dinámico -->
                            </div>
                        </div>

                        <!-- Panel: MCQs Buscados / Consultados Recientemente -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                            <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                    <h3 class="font-bold text-slate-800 text-sm">MCQs Buscados / Consultados Recientemente</h3>
                                </div>
                                <button onclick="clearRecentSearches()" class="text-[11px] text-slate-400 hover:text-slate-600 font-medium">Limpiar historial</button>
                            </div>
                            <div id="recent-searched-list" class="p-4 space-y-3 divide-y divide-slate-100 flex-grow">
                                <!-- Dinámico -->
                            </div>
                        </div>

                    </div>

                    <!-- Accesos Rápidos por Temporada -->
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">Acceso Rápido a Temporadas</h3>
                                <p class="text-xs text-slate-500">Haz clic en una temporada para explorar sus MCQs divididos por marca</p>
                            </div>
                        </div>
                        <div id="dashboard-season-cards" class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <!-- Dinámico -->
                        </div>
                    </div>
                </section>

                <!-- ========================================== -->
                <!-- VISTA 2: DIRECTORIO POR TEMPORADAS Y MARCA -->
                <!-- ========================================== -->
                <section id="view-seasons" class="hidden space-y-6">
                    <!-- Selector de Temporadas -->
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center space-x-2 flex-wrap gap-y-2" id="season-tabs-container">
                            <!-- Botones de Temporadas: ALL, SP26, FA26, SP27, FA27 -->
                        </div>
                        <button onclick="promptAddSeason()" class="admin-only bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold flex items-center space-x-1.5 transition border border-slate-300">
                            <i class="fa-solid fa-calendar-plus text-red-600"></i>
                            <span>+ Temporada</span>
                        </button>
                    </div>

                    <!-- Tarjetas de Filtro por Marca (Patagonia, TNF, Kühl, Engelbert Strauss) -->
                    <div id="brand-cards-filter" class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                        <!-- Dinámico -->
                    </div>

                    <!-- Barra de Búsqueda Secundaria y Categoría -->
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="relative w-full sm:w-80">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                <i class="fa-solid fa-filter"></i>
                            </span>
                            <input type="text" id="directory-search-input" oninput="renderSeasonsDirectory()" placeholder="Filtrar por código de estilo o caja..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
                            <select id="directory-category-filter" onchange="renderSeasonsDirectory()" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="">Todas las Categorías</option>
                                <option value="Pantalones / Trousers">Pantalones / Trousers</option>
                                <option value="Chaquetas / Jackets">Chaquetas / Jackets</option>
                                <option value="Hoodies / Sweatshirts">Hoodies / Sweatshirts</option>
                                <option value="Camisas / Shirts">Camisas / Shirts</option>
                                <option value="Accesorios / Gear">Accesorios / Gear</option>
                            </select>
                            <span id="directory-count-badge" class="bg-slate-200 text-slate-700 text-xs font-bold px-3 py-1.5 rounded-full whitespace-nowrap">0 estilos</span>
                        </div>
                    </div>

                    <!-- Tabla de MCQs Clasificados -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-100/80 text-slate-600 text-[11px] uppercase font-bold tracking-wider border-b border-slate-200">
                                        <th class="py-3.5 px-4">Temporada</th>
                                        <th class="py-3.5 px-4">Marca & Estilo</th>
                                        <th class="py-3.5 px-4">Medidas Prenda [in]</th>
                                        <th class="py-3.5 px-4">Caja Master & Orientación</th>
                                        <th class="py-3.5 px-4 text-center">MCQ (PCS/Caja)</th>
                                        <th class="py-3.5 px-4 text-center">Peso Bruto</th>
                                        <th class="py-3.5 px-4 text-center">Eficiencia & CBM</th>
                                        <th class="py-3.5 px-4 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="directory-table-body" class="divide-y divide-slate-200 text-xs">
                                    <!-- Dinámico -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ========================================== -->
                <!-- VISTA 3: CREAR NUEVO MCQ / BUSCAR ESTILO   -->
                <!-- ========================================== -->
                <section id="view-create-mcq" class="hidden space-y-6">
                    <!-- Buscador de Estilo Existente para Clonar o Consultar -->
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center space-x-2">
                            <i class="fa-solid fa-magnifying-glass text-red-600"></i>
                            <span>Buscar un Estilo Existente (Para consultar, editar o usar como plantilla)</span>
                        </h3>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <input type="text" id="lookup-existing-input" oninput="searchExistingStyleInForm()" placeholder="Escribe el código de estilo (ej. Nuptse, Baggies, Rydr, motion 2020)..." class="flex-grow bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div id="lookup-existing-results" class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            <!-- Resultados dinámicos -->
                        </div>
                    </div>

                    <!-- Formulario de Creación / Edición de MCQ -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div id="form-mcq-header" class="bg-appDark text-white px-6 py-4 flex items-center justify-between border-b-4 border-red-600">
                            <div class="flex items-center space-x-3">
                                <div id="form-brand-badge" class="bg-red-600 px-2.5 py-1.5 rounded-lg text-white font-black text-xs uppercase">
                                    PTG
                                </div>
                                <div>
                                    <h3 id="form-mcq-title" class="font-bold text-base">Registrar Nuevo MCQ Oficial</h3>
                                    <p class="text-xs text-slate-400">Asigna temporada, marca, dimensiones y valida la cabida</p>
                                </div>
                            </div>
                            <button type="button" onclick="openNewMcqClean()" class="text-xs bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-1.5 rounded-lg border border-slate-700">
                                Limpiar Formulario
                            </button>
                        </div>

                        <div class="p-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
                            <form id="mcq-form" onsubmit="handleMcqFormSubmit(event)" class="lg:col-span-7 space-y-5">
                                <input type="hidden" id="test-id">

                                <!-- 1. Temporada y Marca -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Temporada *</label>
                                        <select id="season-select" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-red-500 outline-none">
                                            <!-- SP26, FA26, SP27, FA27 -->
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Marca *</label>
                                        <select id="brand-select" onchange="onBrandChange()" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-red-500 outline-none">
                                            <!-- Patagonia, TNF, Kühl, Engelbert Strauss -->
                                        </select>
                                    </div>
                                </div>

                                <!-- 2. Estilo y Categoría -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">Código / Nombre de Estilo *</label>
                                        <input type="text" id="style-code" required placeholder="Ej. Men's Baggies Lights #58046" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-red-500 outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">Categoría de Prenda *</label>
                                        <select id="garment-category" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-red-500 outline-none">
                                            <option value="Pantalones / Trousers">Pantalones / Trousers</option>
                                            <option value="Chaquetas / Jackets">Chaquetas / Jackets</option>
                                            <option value="Hoodies / Sweatshirts">Hoodies / Sweatshirts</option>
                                            <option value="Camisas / Shirts">Camisas / Shirts</option>
                                            <option value="Accesorios / Gear">Accesorios / Gear</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- 3. Dimensiones Prenda y Compresión -->
                                <div class="space-y-3">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-red-600 border-b border-slate-200 pb-1">Dimensiones de Prenda Doblada [in] & Peso [g]</h4>
                                    <div class="grid grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Largo (L) [in]</label>
                                            <input type="number" step="0.1" min="0.1" id="garment-l" value="14.5" required oninput="runLiveCalculation()" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Ancho (W) [in]</label>
                                            <input type="number" step="0.1" min="0.1" id="garment-w" value="10.5" required oninput="runLiveCalculation()" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Espesor (H) [in]</label>
                                            <input type="number" step="0.05" min="0.05" id="garment-h" value="1.5" required oninput="runLiveCalculation()" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Peso Unitario Neto [g] *</label>
                                            <input type="number" step="1" min="1" id="unit-weight" value="650" required oninput="runLiveCalculation()" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Cantidad Orden / Lote (PCS)</label>
                                            <input type="number" min="1" id="order-qty" value="2400" required oninput="runLiveCalculation()" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono">
                                        </div>
                                    </div>

                                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                                        <div>
                                            <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                                                <span>Factor Compresión / Holgura:</span>
                                                <span id="compression-label" class="font-mono text-red-600 font-bold">0%</span>
                                            </div>
                                            <input type="range" id="compression-factor" min="-35" max="20" step="1" value="0" oninput="runLiveCalculation()" class="w-full accent-red-600 cursor-pointer">
                                        </div>
                                        <div>
                                            <label class="inline-flex items-center space-x-2 cursor-pointer">
                                                <input type="checkbox" id="lock-flat-orientation" onchange="runLiveCalculation()" class="rounded text-red-600 w-4 h-4">
                                                <span class="text-xs font-semibold text-slate-700">Mantener prenda plana (Eje H)</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- 4. Selección de Caja Master -->
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between border-b border-slate-200 pb-1">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-red-600">Caja Master Autorizada</h4>
                                        <div class="flex space-x-2">
                                            <button type="button" onclick="autoOptimizeBox()" class="text-[11px] bg-amber-100 hover:bg-amber-200 text-amber-800 px-2.5 py-1 rounded-md font-bold border border-amber-300">
                                                <i class="fa-solid fa-wand-magic-sparkles mr-1"></i>Auto-Optimizar Caja
                                            </button>
                                            <button type="button" onclick="openNewBoxModal()" class="text-[11px] text-red-600 hover:underline font-bold">
                                                + Nueva Caja
                                            </button>
                                        </div>
                                    </div>
                                    <select id="box-catalog-select" onchange="onBoxCatalogChange()" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-semibold text-slate-800">
                                        <!-- Dinámico -->
                                    </select>
                                    <div class="grid grid-cols-5 gap-2 text-xs bg-slate-50 p-3 rounded-xl border border-slate-200">
                                        <div>
                                            <span class="text-[10px] text-slate-500 block font-bold">Largo [in]</span>
                                            <input type="number" step="0.1" id="box-l" oninput="onCustomBoxInput()" class="w-full bg-white border border-slate-300 rounded px-2 py-1 font-mono font-bold">
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-500 block font-bold">Ancho [in]</span>
                                            <input type="number" step="0.1" id="box-w" oninput="onCustomBoxInput()" class="w-full bg-white border border-slate-300 rounded px-2 py-1 font-mono font-bold">
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-500 block font-bold">Alto [in]</span>
                                            <input type="number" step="0.1" id="box-h" oninput="onCustomBoxInput()" class="w-full bg-white border border-slate-300 rounded px-2 py-1 font-mono font-bold">
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-500 block font-bold">Tara [g]</span>
                                            <input type="number" step="10" id="box-tare" oninput="onCustomBoxInput()" class="w-full bg-white border border-slate-300 rounded px-2 py-1 font-mono font-bold">
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-500 block font-bold">Máx [kg]</span>
                                            <input type="number" step="0.5" id="max-weight-limit" oninput="onCustomBoxInput()" class="w-full bg-white border border-slate-300 rounded px-2 py-1 font-mono font-bold">
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-2 flex space-x-3">
                                    <button type="submit" class="flex-grow bg-red-600 hover:bg-red-700 text-white py-3 rounded-xl text-xs font-bold shadow-lg transition flex items-center justify-center space-x-2">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        <span>Guardar MCQ en Temporada</span>
                                    </button>
                                    <button type="button" onclick="openFormInEmbeddedSimulator()" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-3 rounded-xl text-xs font-bold transition flex items-center space-x-2">
                                        <i class="fa-solid fa-cube text-red-400"></i>
                                        <span>Abrir en Simulador 3D</span>
                                    </button>
                                </div>
                            </form>

                            <!-- Panel Derecho de Resultado en Vivo -->
                            <div class="lg:col-span-5 bg-appDark text-white p-5 rounded-2xl flex flex-col justify-between space-y-4 border border-slate-800">
                                <div>
                                    <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-3">
                                        <span class="text-xs font-bold uppercase tracking-wider text-red-500">Resultado MCQ Calculado</span>
                                        <span id="res-orientation-badge" class="bg-red-500/20 text-red-300 text-[10px] px-2 py-0.5 rounded font-mono">L×W×H</span>
                                    </div>
                                    <div class="bg-slate-900 p-4 rounded-xl border border-red-500/40 text-center mb-4">
                                        <p class="text-[11px] text-slate-400 uppercase font-semibold">Master Carton Quantity (PCS / Caja)</p>
                                        <h3 id="res-recommended-pcs" class="text-4xl font-black text-red-500 mt-1 font-mono">0 PCS</h3>
                                        <p id="res-binding-constraint" class="text-[11px] text-amber-300 mt-1">Calculando...</p>
                                    </div>
                                    <div class="space-y-2 text-xs">
                                        <div class="bg-slate-800/80 p-2.5 rounded-xl flex justify-between">
                                            <span class="text-slate-400">Acomodo 3D (Col × Fil × Capas):</span>
                                            <span id="res-grid-dims" class="font-mono font-bold">0 × 0 × 0</span>
                                        </div>
                                        <div class="bg-slate-800/80 p-2.5 rounded-xl flex justify-between">
                                            <span class="text-slate-400">Peso Neto / Bruto por Caja:</span>
                                            <span id="res-weights-combined" class="font-mono font-bold text-emerald-400">0 kg</span>
                                        </div>
                                        <div class="bg-slate-800/80 p-2.5 rounded-xl flex justify-between">
                                            <span class="text-slate-400">Eficiencia Volumétrica:</span>
                                            <span id="res-efficiency" class="font-mono font-bold text-blue-400">0%</span>
                                        </div>
                                        <div class="bg-slate-800/80 p-2.5 rounded-xl flex justify-between">
                                            <span class="text-slate-400">Total Cajas & CBM Orden:</span>
                                            <span id="res-total-cartons-cbm" class="font-mono font-bold text-purple-300">0 cajas (0 m³)</span>
                                        </div>
                                    </div>
                                    <div id="calculator-warning-box" class="hidden mt-3 p-2.5 bg-amber-500/20 border border-amber-500/40 rounded-lg text-[11px] text-amber-300 flex items-center space-x-2">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        <span id="calculator-warning-text"></span>
                                    </div>
                                </div>
                                <div class="text-[11px] text-slate-400 border-t border-slate-800 pt-3 flex justify-between">
                                    <span>Holgura libre en caja:</span>
                                    <span id="res-clearance-text" class="font-mono text-emerald-400">L: 0" | W: 0" | H: 0"</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ========================================== -->
                <!-- VISTA 4: SIMULADOR 3D DE PIEZAS POR CAJA   -->
                <!-- ========================================== -->
                <section id="view-simulator" class="hidden space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                        <!-- Controles del Simulador (4 cols) -->
                        <div class="lg:col-span-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200 space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                                <h3 class="font-bold text-slate-800 text-sm flex items-center space-x-2">
                                    <i class="fa-solid fa-sliders text-red-600"></i>
                                    <span>Parámetros de Simulación 3D</span>
                                </h3>
                                <span id="sim-pcs-badge" class="bg-red-600 text-white font-mono font-black text-xs px-2.5 py-1 rounded-lg">0 PCS</span>
                            </div>

                            <div class="space-y-3 text-xs">
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">Cargar Estilo MCQ Existente (Opcional)</label>
                                    <select id="sim-load-mcq" onchange="loadMcqIntoSimulator(this.value)" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs">
                                        <option value="">-- Simulación Libre --</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <label class="block font-semibold text-slate-600 mb-1">Prenda L [in]</label>
                                        <input type="number" step="0.1" id="sim-g-l" value="15.0" oninput="updateEmbeddedSimulator()" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 font-mono">
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-600 mb-1">Prenda W [in]</label>
                                        <input type="number" step="0.1" id="sim-g-w" value="11.0" oninput="updateEmbeddedSimulator()" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 font-mono">
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-600 mb-1">Prenda H [in]</label>
                                        <input type="number" step="0.05" id="sim-g-h" value="1.4" oninput="updateEmbeddedSimulator()" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 font-mono">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block font-semibold text-slate-600 mb-1">Peso Prenda [g]</label>
                                        <input type="number" step="10" id="sim-u-w" value="750" oninput="updateEmbeddedSimulator()" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 font-mono">
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-600 mb-1">Compresión (%)</label>
                                        <input type="number" step="1" min="-35" max="20" id="sim-comp" value="0" oninput="updateEmbeddedSimulator()" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 font-mono">
                                    </div>
                                </div>

                                <div class="border-t border-slate-200 pt-3">
                                    <label class="block font-semibold text-slate-700 mb-1">Dimensiones Internas Caja Master [in]</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div>
                                            <span class="text-[10px] text-slate-400 block">Largo Caja</span>
                                            <input type="number" step="0.1" id="sim-b-l" value="24.0" oninput="updateEmbeddedSimulator()" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 font-mono font-bold">
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-400 block">Ancho Caja</span>
                                            <input type="number" step="0.1" id="sim-b-w" value="16.0" oninput="updateEmbeddedSimulator()" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 font-mono font-bold">
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-400 block">Alto Caja</span>
                                            <input type="number" step="0.1" id="sim-b-h" value="18.0" oninput="updateEmbeddedSimulator()" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 font-mono font-bold">
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <span class="text-[10px] text-slate-400 block">Tara Caja [g]</span>
                                        <input type="number" step="10" id="sim-b-tare" value="900" oninput="updateEmbeddedSimulator()" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 font-mono">
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400 block">Límite Ergo [kg]</span>
                                        <input type="number" step="0.5" id="sim-max-kg" value="16.0" oninput="updateEmbeddedSimulator()" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 font-mono">
                                    </div>
                                </div>

                                <label class="inline-flex items-center space-x-2 cursor-pointer pt-1">
                                    <input type="checkbox" id="sim-lock-flat" onchange="updateEmbeddedSimulator()" class="rounded text-red-600 w-4 h-4">
                                    <span class="font-semibold text-slate-700">Mantener prenda plana (Eje H vertical)</span>
                                </label>

                                <!-- Resumen de Simulación -->
                                <div class="bg-slate-900 text-white p-3.5 rounded-xl space-y-1.5 font-mono text-[11px]">
                                    <div class="flex justify-between"><span class="text-slate-400">Orientación:</span><span id="sim-res-ori" class="text-red-400 font-bold">L×W×H</span></div>
                                    <div class="flex justify-between"><span class="text-slate-400">Acomodo:</span><span id="sim-res-grid">0×0×0</span></div>
                                    <div class="flex justify-between"><span class="text-slate-400">Peso Bruto:</span><span id="sim-res-weight" class="text-amber-400">0.00 kg</span></div>
                                    <div class="flex justify-between"><span class="text-slate-400">Eficiencia:</span><span id="sim-res-eff" class="text-blue-400">0.0%</span></div>
                                    <div class="flex justify-between"><span class="text-slate-400">Holgura (L/W/H):</span><span id="sim-res-clear" class="text-emerald-400">0" / 0" / 0"</span></div>
                                </div>

                                <button type="button" onclick="transferSimToCreateMcq()" class="w-full bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-xl font-bold shadow transition flex items-center justify-center space-x-2">
                                    <i class="fa-solid fa-share-from-square"></i>
                                    <span>Crear MCQ con esta Simulación</span>
                                </button>
                            </div>
                        </div>

                        <!-- Visor 3D Interactivo (8 cols) -->
                        <div class="lg:col-span-8 bg-slate-900 rounded-2xl shadow-xl border border-slate-800 overflow-hidden flex flex-col h-[560px]">
                            <div class="bg-appDark px-5 py-3 border-b border-slate-800 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-300">
                                <div class="flex items-center space-x-2">
                                    <i class="fa-solid fa-cube text-red-500"></i>
                                    <span class="font-bold text-white">Visor 3D en Tiempo Real</span>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-slate-400">Capas visibles:</span>
                                        <input type="range" id="sim-layer-slider" min="1" max="10" value="10" oninput="updateSimLayerVisibility()" class="w-24 accent-red-600 cursor-pointer">
                                        <span id="sim-layer-label" class="font-mono text-white font-bold">Todas</span>
                                    </div>
                                    <button type="button" onclick="toggleSimWireframe()" class="bg-slate-800 hover:bg-slate-700 px-2.5 py-1 rounded border border-slate-700 text-white font-semibold">
                                        Wireframe
                                    </button>
                                </div>
                            </div>
                            <div id="embedded-3d-container" class="flex-grow w-full relative bg-slate-950"></div>
                            <div class="bg-appDark px-5 py-2.5 border-t border-slate-800 flex justify-between text-[11px] text-slate-400">
                                <span>Usa clic izquierdo para rotar &bull; Rueda para zoom &bull; Clic derecho para desplazar</span>
                                <span class="text-red-400 font-semibold">1 unidad = 1 pulgada [in]</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ========================================== -->
                <!-- VISTA 5: PANEL DE ADMINISTRACIÓN (ADMIN)   -->
                <!-- ========================================== -->
                <section id="view-admin" class="hidden space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Gestión de Usuarios -->
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 space-y-4">
                            <h3 class="font-bold text-slate-800 text-sm flex items-center space-x-2 border-b border-slate-200 pb-3">
                                <i class="fa-solid fa-users text-red-600"></i>
                                <span>Control de Usuarios y Permisos</span>
                            </h3>
                            <form onsubmit="handleAddUser(event)" class="grid grid-cols-1 sm:grid-cols-4 gap-2 text-xs">
                                <input type="text" id="new-user-username" required placeholder="Usuario (login)" class="bg-slate-50 border border-slate-300 rounded-lg px-3 py-2">
                                <input type="text" id="new-user-name" required placeholder="Nombre completo" class="bg-slate-50 border border-slate-300 rounded-lg px-3 py-2">
                                <input type="password" id="new-user-pass" required placeholder="Contraseña" class="bg-slate-50 border border-slate-300 rounded-lg px-3 py-2">
                                <div class="flex space-x-1">
                                    <select id="new-user-role" class="bg-slate-50 border border-slate-300 rounded-lg px-2 py-2 flex-grow font-semibold">
                                        <option value="USER">USUARIO</option>
                                        <option value="ADMIN">ADMIN</option>
                                    </select>
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg font-bold">+</button>
                                </div>
                            </form>
                            <div class="divide-y divide-slate-200 text-xs" id="admin-users-list">
                                <!-- Dinámico -->
                            </div>
                        </div>

                        <!-- Gestión de Marcas y Temporadas -->
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                                <h3 class="font-bold text-slate-800 text-sm flex items-center space-x-2">
                                    <i class="fa-solid fa-tags text-red-600"></i>
                                    <span>Marcas Registradas</span>
                                </h3>
                                <button onclick="openNewBrandModal()" class="bg-slate-800 hover:bg-slate-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold">
                                    + Agregar Marca
                                </button>
                            </div>
                            <div id="admin-brands-list" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                                <!-- Dinámico -->
                            </div>
                        </div>
                    </div>
                </section>

            </main>
        </div>
    </div>

    <!-- Modal para Agregar Nueva Marca -->
    <div id="new-brand-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 no-print">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <h3 class="font-bold text-slate-800 text-base">Registrar Nueva Marca</h3>
                <button onclick="closeNewBrandModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="new-brand-form" onsubmit="handleNewBrandSubmit(event)" class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nombre de la Marca *</label>
                    <input type="text" id="new-brand-name" required placeholder="Ej. Arc'teryx / Columbia" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Siglas / Badge *</label>
                        <input type="text" id="new-brand-badge" required maxlength="5" placeholder="Ej. ARC" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm uppercase font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Color Identificador *</label>
                        <input type="color" id="new-brand-color" value="#0f766e" class="w-full h-9 bg-slate-50 border border-slate-300 rounded-lg p-1 cursor-pointer">
                    </div>
                </div>
                <div class="pt-2 flex space-x-2">
                    <button type="button" onclick="closeNewBrandModal()" class="w-1/2 bg-slate-200 text-slate-700 py-2.5 rounded-lg font-semibold">Cancelar</button>
                    <button type="submit" class="w-1/2 bg-red-600 text-white py-2.5 rounded-lg font-semibold">Guardar Marca</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para Añadir Nueva Caja Master -->
    <div id="box-catalog-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 no-print">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <h3 class="font-bold text-slate-800 text-base">Registrar Nueva Caja Master</h3>
                <button onclick="closeNewBoxModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="new-box-form" onsubmit="handleNewBoxSubmit(event)" class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nombre / Referencia de Caja *</label>
                    <input type="text" id="new-box-name" required placeholder="Ej. Caja Master Grande #5" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Largo [in] *</label>
                        <input type="number" step="0.1" id="new-box-l" required value="24.0" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Ancho [in] *</label>
                        <input type="number" step="0.1" id="new-box-w" required value="16.0" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Alto [in] *</label>
                        <input type="number" step="0.1" id="new-box-h" required value="18.0" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2 py-2 text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Peso Tara [g] *</label>
                        <input type="number" step="10" id="new-box-tare" required value="900" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2 py-2 text-sm font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Límite Peso [kg] *</label>
                        <input type="number" step="0.5" id="new-box-max" required value="16.0" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2 py-2 text-sm">
                    </div>
                </div>
                <div class="pt-2 flex space-x-2">
                    <button type="button" onclick="closeNewBoxModal()" class="w-1/2 bg-slate-200 text-slate-700 py-2.5 rounded-lg font-semibold">Cancelar</button>
                    <button type="submit" class="w-1/2 bg-red-600 text-white py-2.5 rounded-lg font-semibold">Guardar Caja</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Reporte / Ficha Técnica de MCQ -->
    <div id="detail-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[92vh] flex flex-col overflow-hidden">
            <div id="detail-modal-header" class="bg-appDark text-white px-6 py-4 flex items-center justify-between border-b-4 border-red-600">
                <div class="flex items-center space-x-3">
                    <div id="detail-brand-badge" class="bg-red-600 px-2.5 py-1.5 rounded-lg text-white font-black text-xs uppercase">
                        BRAND
                    </div>
                    <div>
                        <h3 id="detail-title" class="font-bold text-base">Ficha Oficial MCQ</h3>
                        <p id="detail-subtitle" class="text-xs text-slate-400">Temporada &bull; Especificación de Empaque</p>
                    </div>
                </div>
                <button onclick="closeDetailModal()" class="text-slate-400 hover:text-white p-2 rounded-lg no-print">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <div id="detail-content" class="p-6 overflow-y-auto flex-grow space-y-4 text-sm"></div>

            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex flex-wrap items-center justify-between gap-3 no-print">
                <div class="flex space-x-2">
                    <button onclick="copySummaryToClipboard()" class="bg-slate-800 hover:bg-slate-900 text-white px-3.5 py-2 rounded-lg text-xs font-semibold flex items-center space-x-1.5">
                        <i class="fa-solid fa-copy"></i>
                        <span>Copiar Resumen</span>
                    </button>
                    <button onclick="openDetailInSimulator()" class="bg-red-600 hover:bg-red-700 text-white px-3.5 py-2 rounded-lg text-xs font-semibold flex items-center space-x-1.5">
                        <i class="fa-solid fa-cube"></i>
                        <span>Simular en 3D</span>
                    </button>
                </div>
                <div class="flex space-x-2">
                    <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-700 text-white px-3.5 py-2 rounded-lg text-xs font-semibold flex items-center space-x-1.5">
                        <i class="fa-solid fa-print"></i>
                        <span>Imprimir</span>
                    </button>
                    <button onclick="closeDetailModal()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-3.5 py-2 rounded-lg text-xs font-semibold">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MOTOR JAVASCRIPT + THREE.JS + LARAVEL API  -->
    <!-- ========================================== -->
    <script>
        const SESSION_STORAGE_KEY = 'mcq_portal_session_v1';
        const RECENT_SEARCH_KEY = 'mcq_portal_recent_searches_v1';
        const INCH3_TO_CBM = 0.000016387064;
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        let users = [];
        let currentUser = null;
        let seasons = [];
        let brands = [];
        let masterBoxes = [];
        let tests = [];
        let recentSearchedIds = [];

        // Filtros activos en la vista de Temporadas y Marcas
        let activeSeasonFilter = 'ALL';
        let activeBrandFilter = 'ALL';
        let currentActiveTestId = null;

        // Variables Three.js del Simulador
        let scene, camera, renderer, controls, animationFrameId;
        let simGarmentMeshes = [];
        let simWireframe = false;

        // Helper para peticiones al Backend Laravel + Supabase
        async function apiRequest(url, method = 'GET', body = null) {
            const options = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                }
            };
            if (body) options.body = JSON.stringify(body);
            const response = await fetch(url, options);
            if (!response.ok) {
                const errText = await response.text();
                throw new Error(errText || 'Error en la petición al servidor');
            }
            return response.json();
        }

        window.onload = async function() {
            await loadAllData();
            checkSession();
            document.addEventListener('click', (e) => {
                const dd = document.getElementById('quick-search-dropdown');
                const inp = document.getElementById('global-style-search');
                if (dd && !dd.contains(e.target) && e.target !== inp) {
                    dd.classList.add('hidden');
                }
            });
        };

        async function loadAllData() {
            try {
                const data = await apiRequest('/api/mcq/bootstrap');
                users = data.users || [];
                seasons = data.seasons || [];
                brands = data.brands || [];
                masterBoxes = data.masterBoxes || [];
                tests = data.tests || [];
                recentSearchedIds = JSON.parse(localStorage.getItem(RECENT_SEARCH_KEY)) || [];
            } catch (error) {
                console.error('Error cargando datos desde Supabase:', error);
                showToast('Error al sincronizar con la base de datos Supabase', 'info');
            }
        }

        function saveAllData() {
            localStorage.setItem(RECENT_SEARCH_KEY, JSON.stringify(recentSearchedIds));
        }

        // ==========================================
        // AUTENTICACIÓN Y CONTROL DE ROLES
        // ==========================================
        function fillDemoLogin(u, p) {
            document.getElementById('login-username').value = u;
            document.getElementById('login-password').value = p;
        }

        async function handleLogin(event) {
            event.preventDefault();
            const username = document.getElementById('login-username').value.trim().toLowerCase();
            const password = document.getElementById('login-password').value;

            try {
                const found = await apiRequest('/api/mcq/login', 'POST', { username, password });
                document.getElementById('login-error').classList.add('hidden');
                currentUser = found;
                localStorage.setItem(SESSION_STORAGE_KEY, JSON.stringify(currentUser));
                await loadAllData();
                applyUserSessionUI();
                showToast(`Bienvenido, ${currentUser.name} (${currentUser.role})`, 'success');
            } catch (err) {
                document.getElementById('login-error').classList.remove('hidden');
            }
        }

        function checkSession() {
            const saved = localStorage.getItem(SESSION_STORAGE_KEY);
            if (saved) {
                currentUser = JSON.parse(saved);
                applyUserSessionUI();
            } else {
                document.getElementById('login-screen').classList.remove('hidden');
                document.getElementById('app-wrapper').classList.add('hidden');
            }
        }

        function handleLogout() {
            currentUser = null;
            localStorage.removeItem(SESSION_STORAGE_KEY);
            document.getElementById('app-wrapper').classList.add('hidden');
            document.getElementById('login-screen').classList.remove('hidden');
        }

        function applyUserSessionUI() {
            document.getElementById('login-screen').classList.add('hidden');
            document.getElementById('app-wrapper').classList.remove('hidden');

            document.getElementById('user-display-name').innerText = currentUser.name;
            document.getElementById('user-avatar').innerText = currentUser.name.substring(0, 2).toUpperCase();
            
            const roleBadge = document.getElementById('user-role-badge');
            roleBadge.innerText = currentUser.role;
            if (currentUser.role === 'ADMIN') {
                roleBadge.className = 'inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-red-500/20 text-red-400 border border-red-500/30 mt-0.5';
                document.getElementById('admin-nav-section').classList.remove('hidden');
                document.querySelectorAll('.admin-only').forEach(el => el.classList.remove('hidden'));
            } else {
                roleBadge.className = 'inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 mt-0.5';
                document.getElementById('admin-nav-section').classList.add('hidden');
                document.querySelectorAll('.admin-only').forEach(el => el.classList.add('hidden'));
            }

            populateFormSelects();
            refreshAllViews();
            switchView('dashboard');
        }

        // ==========================================
        // NAVEGACIÓN ENTRE VISTAS
        // ==========================================
        function switchView(viewName) {
            const views = ['dashboard', 'seasons', 'create-mcq', 'simulator', 'admin'];
            views.forEach(v => {
                document.getElementById(`view-${v}`).classList.add('hidden');
                const btn = document.getElementById(`nav-${v}`);
                if (btn) {
                    btn.className = 'nav-btn w-full flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:bg-slate-800 transition';
                }
            });

            document.getElementById(`view-${viewName}`).classList.remove('hidden');
            const activeBtn = document.getElementById(`nav-${viewName}`);
            if (activeBtn) {
                activeBtn.className = 'nav-btn w-full flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition bg-red-600 text-white shadow';
            }

            const titles = {
                'dashboard': 'Vista General: MCQs Buscados y Creados Recientemente',
                'seasons': 'Directorio de MCQ por Temporadas y Marcas',
                'create-mcq': 'Crear Nuevo MCQ / Buscar Estilo Existente',
                'simulator': 'Simulador 3D de Piezas por Caja Master',
                'admin': 'Panel de Administración de Usuarios y Catálogos'
            };
            document.getElementById('current-view-title').innerText = titles[viewName] || 'Portal Global MCQ';

            if (viewName === 'dashboard') renderDashboard();
            if (viewName === 'seasons') renderSeasonsDirectory();
            if (viewName === 'create-mcq') {
                runLiveCalculation();
                searchExistingStyleInForm();
            }
            if (viewName === 'simulator') {
                populateSimulatorMcqDropdown();
                setTimeout(() => initEmbedded3D(), 60);
            }
            if (viewName === 'admin') renderAdminPanel();
        }

        function refreshAllViews() {
            renderDashboard();
            renderSeasonsDirectory();
            renderAdminPanel();
        }

        function getBrand(brandId) {
            return brands.find(b => b.id === brandId) || { name: 'Otra Marca', color: '#64748b', badge: '??' };
        }

        // ==========================================
        // MOTOR GEOMÉTRICO DE CABIDA Y CBM
        // ==========================================
        function calculateMetrics(data) {
            const { gL, gW, gH, uW, oQty, bL, bW, bH, bTare, maxKg, compressionPct = 0, lockFlat = false } = data;
            const effGH = Math.max(0.05, Number((gH * (1 + (compressionPct / 100))).toFixed(3)));

            const allOrientations = [
                { l: gL, w: gW, h: effGH, oriName: 'L × W × H (Plana)' },
                { l: gW, w: gL, h: effGH, oriName: 'W × L × H (Plana 90°)' },
                { l: gL, w: effGH, h: gW, oriName: 'L × H × W (De Canto)' },
                { l: gW, w: effGH, h: gL, oriName: 'W × H × L (De Canto 90°)' },
                { l: effGH, w: gL, h: gW, oriName: 'H × L × W (Vertical)' },
                { l: effGH, w: gW, h: gL, oriName: 'H × W × L (Vertical 90°)' }
            ];

            const orientations = lockFlat ? allOrientations.slice(0, 2) : allOrientations;

            let maxPieces = 0;
            let bestGrid = { cols: 0, rows: 0, layers: 0, total: 0 };
            let bestOri = orientations[0];

            orientations.forEach(ori => {
                if (ori.l <= 0 || ori.w <= 0 || ori.h <= 0 || bL <= 0 || bW <= 0 || bH <= 0) return;
                const cols = Math.floor(bL / ori.l);
                const rows = Math.floor(bW / ori.w);
                const layers = Math.floor(bH / ori.h);
                const total = cols * rows * layers;
                if (total > maxPieces) {
                    maxPieces = total;
                    bestGrid = { cols, rows, layers, total };
                    bestOri = ori;
                }
            });

            const fitsInBox = maxPieces > 0;
            let ergonomicLimitPieces = maxPieces;
            let weightConstrained = false;

            if (fitsInBox && maxKg && maxKg > 0 && uW > 0) {
                const maxGrams = maxKg * 1000;
                const spatialGrossGrams = (maxPieces * uW) + bTare;
                if (spatialGrossGrams > maxGrams) {
                    weightConstrained = true;
                    const allowedNet = Math.max(0, maxGrams - bTare);
                    ergonomicLimitPieces = Math.floor(allowedNet / uW);
                    if (ergonomicLimitPieces < 1) ergonomicLimitPieces = 1;
                }
            }

            const finalRecommendedPcs = fitsInBox ? Math.min(maxPieces, ergonomicLimitPieces) : 0;
            const finalNetGrams = finalRecommendedPcs * uW;
            const finalGrossGrams = finalRecommendedPcs > 0 ? (finalNetGrams + bTare) : 0;
            const finalGrossKg = finalGrossGrams / 1000;

            const boxVolumeIn3 = bL * bW * bH;
            const garmentVolumeIn3 = (gL * gW * effGH) * finalRecommendedPcs;
            let efficiency = (boxVolumeIn3 > 0 && finalRecommendedPcs > 0) ? (garmentVolumeIn3 / boxVolumeIn3) * 100 : 0;
            if (efficiency > 100) efficiency = 100;

            const totalCartons = finalRecommendedPcs > 0 ? Math.ceil(oQty / finalRecommendedPcs) : 0;
            const unitBoxCBM = boxVolumeIn3 * INCH3_TO_CBM;
            const totalOrderCBM = unitBoxCBM * totalCartons;

            const clearanceL = fitsInBox ? Math.max(0, bL - (bestGrid.cols * bestOri.l)).toFixed(1) : bL.toFixed(1);
            const clearanceW = fitsInBox ? Math.max(0, bW - (bestGrid.rows * bestOri.w)).toFixed(1) : bW.toFixed(1);
            const clearanceH = fitsInBox ? Math.max(0, bH - (bestGrid.layers * bestOri.h)).toFixed(1) : bH.toFixed(1);

            let bindingConstraintText = 'Limitado por capacidad geométrica de caja';
            if (!fitsInBox) bindingConstraintText = 'Incompatibilidad: Prenda excede dimensiones de caja';
            else if (weightConstrained && finalRecommendedPcs < maxPieces) bindingConstraintText = 'Limitado por peso máximo ergonómico';

            return {
                fitsInBox,
                effGH,
                recommendedPcs: finalRecommendedPcs,
                spatialMax: maxPieces,
                grid: bestGrid,
                bestOri,
                clearance: { l: clearanceL, w: clearanceW, h: clearanceH },
                netWeightGrams: finalNetGrams,
                grossWeightKg: finalGrossKg,
                efficiency: efficiency.toFixed(1),
                totalCartons,
                totalOrderCBM,
                weightConstrained: weightConstrained && finalRecommendedPcs < maxPieces,
                bindingConstraintText
            };
        }

        // ==========================================
        // BÚSQUEDA GLOBAL Y REGISTRO DE RECIENTES
        // ==========================================
        function registerRecentSearch(testId) {
            recentSearchedIds = [testId, ...recentSearchedIds.filter(id => id !== testId)].slice(0, 8);
            saveAllData();
            if (!document.getElementById('view-dashboard').classList.contains('hidden')) {
                renderDashboard();
            }
        }

        function clearRecentSearches() {
            recentSearchedIds = [];
            saveAllData();
            renderDashboard();
            showToast('Historial de búsquedas recientes limpiado', 'info');
        }

        function handleGlobalSearchInput(query) {
            const dropdown = document.getElementById('quick-search-dropdown');
            const q = query.trim().toLowerCase();
            if (!q) {
                dropdown.classList.add('hidden');
                return;
            }

            const matches = tests.filter(t => {
                const brand = getBrand(t.brandId);
                return t.styleCode.toLowerCase().includes(q) ||
                    t.id.toLowerCase().includes(q) ||
                    (t.season && t.season.toLowerCase().includes(q)) ||
                    brand.name.toLowerCase().includes(q);
            });

            dropdown.innerHTML = '';
            if (matches.length === 0) {
                dropdown.innerHTML = `<div class="p-3 text-xs text-slate-400 text-center">No se encontraron estilos para "${query}"</div>`;
            } else {
                matches.slice(0, 6).forEach(t => {
                    const b = getBrand(t.brandId);
                    const m = calculateMetrics({
                        gL: t.garmentL, gW: t.garmentW, gH: t.garmentH,
                        uW: t.unitWeightGrams, oQty: t.orderQty,
                        bL: t.boxL, bW: t.boxW, bH: t.boxH,
                        bTare: t.boxTareGrams, maxKg: t.maxWeightLimitKg,
                        compressionPct: t.compressionPct || 0, lockFlat: !!t.lockFlat
                    });

                    const item = document.createElement('div');
                    item.className = 'p-3 hover:bg-slate-50 cursor-pointer flex items-center justify-between transition';
                    item.onclick = () => {
                        dropdown.classList.add('hidden');
                        document.getElementById('global-style-search').value = '';
                        registerRecentSearch(t.id);
                        openDetailModal(t.id);
                    };
                    item.innerHTML = `
                        <div class="flex items-center space-x-2.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold text-white" style="background:${b.color}">${b.badge}</span>
                            <div>
                                <p class="text-xs font-bold text-slate-800">${t.styleCode}</p>
                                <p class="text-[10px] text-slate-500">Temporada: <strong>${t.season || 'SP26'}</strong> &bull; ${t.category}</p>
                            </div>
                        </div>
                        <span class="bg-red-50 text-red-700 font-mono font-black text-xs px-2 py-1 rounded border border-red-200">${m.recommendedPcs} PCS</span>
                    `;
                    dropdown.appendChild(item);
                });
            }
            dropdown.classList.remove('hidden');
        }

        // ==========================================
        // VISTA 1: DASHBOARD GENERAL
        // ==========================================
        function renderDashboard() {
            document.getElementById('kpi-total-mcqs').innerText = tests.length;
            document.getElementById('kpi-total-seasons').innerText = seasons.length;
            document.getElementById('kpi-total-brands').innerText = brands.length;

            let effSum = 0;
            tests.forEach(t => {
                const m = calculateMetrics({
                    gL: t.garmentL, gW: t.garmentW, gH: t.garmentH,
                    uW: t.unitWeightGrams, oQty: t.orderQty,
                    bL: t.boxL, bW: t.boxW, bH: t.boxH,
                    bTare: t.boxTareGrams, maxKg: t.maxWeightLimitKg,
                    compressionPct: t.compressionPct || 0, lockFlat: !!t.lockFlat
                });
                effSum += parseFloat(m.efficiency);
            });
            document.getElementById('kpi-avg-eff').innerText = tests.length ? `${(effSum / tests.length).toFixed(1)}%` : '0%';

            // 1. MCQs Creados Recientemente
            const createdContainer = document.getElementById('recent-created-list');
            createdContainer.innerHTML = '';
            const sortedByDate = [...tests].sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt)).slice(0, 5);

            sortedByDate.forEach(t => {
                createdContainer.appendChild(createMcqCompactCard(t, 'Creado'));
            });

            // 2. MCQs Buscados Recientemente
            const searchedContainer = document.getElementById('recent-searched-list');
            searchedContainer.innerHTML = '';
            const searchedTests = recentSearchedIds.map(id => tests.find(t => t.id === id)).filter(Boolean);

            if (searchedTests.length === 0) {
                searchedContainer.innerHTML = `<p class="text-xs text-slate-400 py-8 text-center">Usa el buscador superior o consulta un estilo para verlo aquí.</p>`;
            } else {
                searchedTests.slice(0, 5).forEach(t => {
                    searchedContainer.appendChild(createMcqCompactCard(t, 'Consultado'));
                });
            }

            // 3. Tarjetas de Temporadas en Dashboard
            const seasonCards = document.getElementById('dashboard-season-cards');
            seasonCards.innerHTML = '';
            seasons.forEach(season => {
                const count = tests.filter(t => (t.season || 'SP26') === season).length;
                const div = document.createElement('div');
                div.className = 'p-4 rounded-xl border border-slate-200 bg-slate-50 hover:bg-red-50/40 hover:border-red-300 cursor-pointer transition flex items-center justify-between';
                div.onclick = () => {
                    activeSeasonFilter = season;
                    activeBrandFilter = 'ALL';
                    switchView('seasons');
                };
                div.innerHTML = `
                    <div>
                        <span class="text-[10px] font-bold uppercase text-red-600 tracking-wider">Temporada</span>
                        <h4 class="text-lg font-black text-slate-900 font-mono">${season}</h4>
                        <p class="text-xs text-slate-500 mt-0.5">${count} estilos MCQ</p>
                    </div>
                    <i class="fa-solid fa-arrow-right text-slate-400"></i>
                `;
                seasonCards.appendChild(div);
            });
        }

        function createMcqCompactCard(t, tagLabel) {
            const brand = getBrand(t.brandId);
            const m = calculateMetrics({
                gL: t.garmentL, gW: t.garmentW, gH: t.garmentH,
                uW: t.unitWeightGrams, oQty: t.orderQty,
                bL: t.boxL, bW: t.boxW, bH: t.boxH,
                bTare: t.boxTareGrams, maxKg: t.maxWeightLimitKg,
                compressionPct: t.compressionPct || 0, lockFlat: !!t.lockFlat
            });

            const row = document.createElement('div');
            row.className = 'pt-3 first:pt-0 flex items-center justify-between gap-2';
            row.innerHTML = `
                <div class="flex items-center space-x-3 min-w-0">
                    <span class="px-2 py-1 rounded-lg text-[10px] font-black text-white uppercase flex-shrink-0" style="background:${brand.color}">${brand.badge}</span>
                    <div class="truncate">
                        <div class="flex items-center space-x-2">
                            <span class="font-mono text-[10px] font-bold bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded">${t.season || 'SP26'}</span>
                            <span class="font-bold text-slate-800 text-xs truncate">${t.styleCode}</span>
                        </div>
                        <p class="text-[11px] text-slate-500 truncate">${brand.name} &bull; Caja: ${t.boxL}"×${t.boxW}"×${t.boxH}"</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2 flex-shrink-0">
                    <span class="bg-red-50 text-red-700 font-mono font-black text-xs px-2.5 py-1 rounded-lg border border-red-200">${m.recommendedPcs} PCS</span>
                    <button onclick="registerRecentSearch('${t.id}'); openDetailModal('${t.id}')" class="p-1.5 text-slate-400 hover:text-red-600 transition" title="Ver Ficha">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                    <button onclick="openMcqDirectlyInSimulator('${t.id}')" class="p-1.5 text-slate-400 hover:text-blue-600 transition" title="Simular en 3D">
                        <i class="fa-solid fa-cube"></i>
                    </button>
                </div>
            `;
            return row;
        }

        // ==========================================
        // VISTA 2: DIRECTORIO POR TEMPORADAS Y MARCA
        // ==========================================
        function getErgonomicStatus(grossKg, maxKg, weightConstrained) {
            const limit = parseFloat(maxKg) || 16.0;
            const gross = parseFloat(grossKg) || 0;
            const ratio = limit > 0 ? (gross / limit) : 0;

            if (weightConstrained || ratio >= 0.95) {
                return {
                    badgeClass: 'bg-red-100 text-red-800 border-red-300',
                    dotClass: 'bg-red-600',
                    label: weightConstrained ? 'Tope Ergo' : 'Al Límite'
                };
            } else if (ratio >= 0.80) {
                return {
                    badgeClass: 'bg-amber-100 text-amber-800 border-amber-300',
                    dotClass: 'bg-amber-500',
                    label: 'Carga Alta'
                };
            }
            return {
                badgeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                dotClass: 'bg-emerald-500',
                label: 'Óptimo'
            };
        }

        function getEfficiencyBadgeClass(effValue) {
            const eff = parseFloat(effValue) || 0;
            if (eff >= 75) return 'bg-emerald-50 text-emerald-700 border border-emerald-200';
            if (eff >= 55) return 'bg-blue-50 text-blue-700 border border-blue-200';
            return 'bg-amber-50 text-amber-700 border border-amber-200';
        }

        function renderSeasonsDirectory() {
            const tabsContainer = document.getElementById('season-tabs-container');
            tabsContainer.innerHTML = '';

            const allSeasonsList = ['ALL', ...seasons];
            allSeasonsList.forEach(s => {
                const isActive = activeSeasonFilter === s;
                const count = s === 'ALL' ? tests.length : tests.filter(t => (t.season || 'SP26') === s).length;
                const btn = document.createElement('button');
                btn.className = isActive
                    ? 'bg-red-600 text-white px-4 py-2 rounded-xl text-xs font-bold shadow flex items-center space-x-2 transition'
                    : 'bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-xs font-semibold flex items-center space-x-2 transition';
                btn.onclick = () => {
                    activeSeasonFilter = s;
                    renderSeasonsDirectory();
                };
                btn.innerHTML = `
                    <span>${s === 'ALL' ? 'Todas las Temporadas' : s}</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] ${isActive ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600'} font-mono">${count}</span>
                `;
                tabsContainer.appendChild(btn);
            });

            const brandCards = document.getElementById('brand-cards-filter');
            brandCards.innerHTML = '';

            const allBrandCard = document.createElement('div');
            const isAllBrands = activeBrandFilter === 'ALL';
            allBrandCard.className = `p-3 rounded-xl border cursor-pointer transition flex items-center justify-between ${isAllBrands ? 'bg-slate-900 text-white border-slate-900 shadow' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'}`;
            allBrandCard.onclick = () => { activeBrandFilter = 'ALL'; renderSeasonsDirectory(); };
            allBrandCard.innerHTML = `
                <div>
                    <p class="text-[10px] uppercase font-bold opacity-75">Filtro Marca</p>
                    <p class="text-xs font-black">Todas las Marcas</p>
                </div>
                <i class="fa-solid fa-layer-group"></i>
            `;
            brandCards.appendChild(allBrandCard);

            brands.forEach(b => {
                const isSelected = activeBrandFilter === b.id;
                const count = tests.filter(t => (activeSeasonFilter === 'ALL' || (t.season || 'SP26') === activeSeasonFilter) && t.brandId === b.id).length;
                const card = document.createElement('div');
                card.className = `p-3 rounded-xl border cursor-pointer transition flex items-center justify-between ${isSelected ? 'ring-2 ring-red-600 bg-white border-red-500 shadow-md' : 'bg-white border-slate-200 hover:bg-slate-50'}`;
                card.onclick = () => { activeBrandFilter = b.id; renderSeasonsDirectory(); };
                card.innerHTML = `
                    <div class="flex items-center space-x-2.5 min-w-0">
                        <span class="px-2 py-1 rounded text-[10px] font-black text-white flex-shrink-0" style="background:${b.color}">${b.badge}</span>
                        <div class="truncate">
                            <p class="text-xs font-bold text-slate-800 truncate">${b.name}</p>
                            <p class="text-[10px] text-slate-500 font-mono">${count} MCQs</p>
                        </div>
                    </div>
                `;
                brandCards.appendChild(card);
            });

            const query = (document.getElementById('directory-search-input')?.value || '').toLowerCase();
            const catFilter = document.getElementById('directory-category-filter')?.value || '';

            const filtered = tests.filter(t => {
                const matchesSeason = activeSeasonFilter === 'ALL' || (t.season || 'SP26') === activeSeasonFilter;
                const matchesBrand = activeBrandFilter === 'ALL' || t.brandId === activeBrandFilter;
                const matchesCat = !catFilter || t.category === catFilter;
                const matchesQuery = !query || t.styleCode.toLowerCase().includes(query) || (t.boxName && t.boxName.toLowerCase().includes(query));
                return matchesSeason && matchesBrand && matchesCat && matchesQuery;
            });

            document.getElementById('directory-count-badge').innerText = `${filtered.length} estilos`;

            const tbody = document.getElementById('directory-table-body');
            tbody.innerHTML = '';

            if (filtered.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" class="py-10 text-center text-slate-400">No hay registros MCQ para los filtros seleccionados.</td></tr>`;
                return;
            }

            filtered.forEach(t => {
                const brand = getBrand(t.brandId);
                const m = calculateMetrics({
                    gL: t.garmentL, gW: t.garmentW, gH: t.garmentH,
                    uW: t.unitWeightGrams, oQty: t.orderQty,
                    bL: t.boxL, bW: t.boxW, bH: t.boxH,
                    bTare: t.boxTareGrams, maxKg: t.maxWeightLimitKg,
                    compressionPct: t.compressionPct || 0, lockFlat: !!t.lockFlat
                });

                const ergo = getErgonomicStatus(m.grossWeightKg, t.maxWeightLimitKg, m.weightConstrained);
                const effClass = getEfficiencyBadgeClass(m.efficiency);
                const canDelete = currentUser && currentUser.role === 'ADMIN';

                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50 transition';
                tr.innerHTML = `
                    <td class="py-3 px-4">
                        <span class="font-mono font-bold text-xs bg-slate-800 text-white px-2.5 py-1 rounded-lg">${t.season || 'SP26'}</span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold text-white uppercase" style="background:${brand.color}">${brand.badge}</span>
                            <div>
                                <span class="font-bold text-slate-800 block">${t.styleCode}</span>
                                <span class="text-[11px] text-slate-500">${brand.name} &bull; ${t.category}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4 font-mono text-slate-600">
                        ${t.garmentL}" × ${t.garmentW}" × ${t.garmentH}"<br>
                        <span class="text-[10px] text-slate-500">${t.unitWeightGrams} g/pcs</span>
                    </td>
                    <td class="py-3 px-4 font-mono text-slate-600">
                        <span class="font-bold text-slate-800 block">${t.boxName || 'Personalizada'}</span>
                        <span class="text-[10px]">${t.boxL}"×${t.boxW}"×${t.boxH}" &bull; ${m.bestOri.oriName.split(' ')[0]}</span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="bg-red-50 text-red-700 font-black px-2.5 py-1 rounded-lg border border-red-200 font-mono text-sm">${m.recommendedPcs} PCS</span>
                    </td>
                    <td class="py-3 px-4 text-center font-mono">
                        <div class="inline-flex flex-col items-center">
                            <span class="font-bold text-slate-800">${m.grossWeightKg.toFixed(2)} / ${t.maxWeightLimitKg} kg</span>
                            <span class="mt-0.5 inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-sans font-bold border ${ergo.badgeClass}">
                                <span class="w-1.5 h-1.5 rounded-full ${ergo.dotClass}"></span>
                                <span>${ergo.label}</span>
                            </span>
                        </div>
                    </td>
                    <td class="py-3 px-4 text-center font-mono">
                        <span class="${effClass} font-bold px-2 py-0.5 rounded">${m.efficiency}%</span>
                        <span class="block text-[10px] text-purple-700 font-semibold mt-0.5">${m.totalOrderCBM.toFixed(2)} m³</span>
                    </td>
                    <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                        <button onclick="registerRecentSearch('${t.id}'); openDetailModal('${t.id}')" class="p-1.5 text-slate-500 hover:text-red-600" title="Ver Ficha">
                            <i class="fa-solid fa-file-lines"></i>
                        </button>
                        <button onclick="openMcqDirectlyInSimulator('${t.id}')" class="p-1.5 text-slate-500 hover:text-blue-600" title="Simular en 3D">
                            <i class="fa-solid fa-cube"></i>
                        </button>
                        <button onclick="cloneMcqRecord('${t.id}')" class="p-1.5 text-slate-500 hover:text-emerald-600" title="Clonar estilo en otra temporada">
                            <i class="fa-solid fa-copy"></i>
                        </button>
                        <button onclick="editMcqRecord('${t.id}')" class="p-1.5 text-slate-500 hover:text-amber-600" title="Editar">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        ${canDelete ? `<button onclick="deleteTest('${t.id}')" class="p-1.5 text-slate-400 hover:text-red-600" title="Eliminar"><i class="fa-solid fa-trash"></i></button>` : ''}
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        async function promptAddSeason() {
            const code = prompt('Ingresa el código de la nueva temporada (Ej. SP28, FA28, HO27):');
            if (!code || !code.trim()) return;
            const formatted = code.trim().toUpperCase();
            if (seasons.includes(formatted)) {
                showToast('Esa temporada ya existe', 'info');
                return;
            }
            try {
                await apiRequest('/api/mcq/seasons', 'POST', { code: formatted });
                await loadAllData();
                populateFormSelects();
                renderSeasonsDirectory();
                showToast(`Temporada ${formatted} agregada en Supabase`, 'success');
            } catch (err) {
                showToast('No se pudo agregar la temporada', 'info');
            }
        }

        // ==========================================
        // VISTA 3: CREAR NUEVO MCQ Y BUSCAR ESTILO
        // ==========================================
        function populateFormSelects() {
            const seasonSel = document.getElementById('season-select');
            const brandSel = document.getElementById('brand-select');
            if (!seasonSel || !brandSel) return;

            seasonSel.innerHTML = seasons.map(s => `<option value="${s}">${s}</option>`).join('');
            brandSel.innerHTML = brands.map(b => `<option value="${b.id}">${b.name}</option>`).join('');
            onBrandChange();
        }

        function onBrandChange() {
            const brandId = document.getElementById('brand-select').value;
            const brand = getBrand(brandId);
            const badge = document.getElementById('form-brand-badge');
            const header = document.getElementById('form-mcq-header');
            if (badge && header) {
                badge.style.backgroundColor = brand.color;
                badge.innerText = brand.badge;
                header.style.borderBottomColor = brand.color;
            }
            populateBoxCatalogDropdown();
        }

        function populateBoxCatalogDropdown(selectedBoxName = null) {
            const brandId = document.getElementById('brand-select').value;
            const boxSelect = document.getElementById('box-catalog-select');
            boxSelect.innerHTML = '';

            const brandBoxes = masterBoxes.filter(b => b.brandId === brandId);
            brandBoxes.forEach(b => {
                const selected = selectedBoxName === b.name ? 'selected' : '';
                boxSelect.innerHTML += `<option value="${b.id}" ${selected}>${b.name}</option>`;
            });
            boxSelect.innerHTML += `<option value="custom" ${selectedBoxName === 'Caja Personalizada' ? 'selected' : ''}>-- Medidas Personalizadas --</option>`;
            onBoxCatalogChange();
        }

        function onBoxCatalogChange() {
            const boxId = document.getElementById('box-catalog-select').value;
            const box = masterBoxes.find(b => b.id === boxId);
            if (box) {
                document.getElementById('box-l').value = box.l;
                document.getElementById('box-w').value = box.w;
                document.getElementById('box-h').value = box.h;
                document.getElementById('box-tare').value = box.tare;
                document.getElementById('max-weight-limit').value = box.maxKg;
            }
            runLiveCalculation();
        }

        function onCustomBoxInput() {
            document.getElementById('box-catalog-select').value = 'custom';
            runLiveCalculation();
        }

        function runLiveCalculation() {
            const comp = parseFloat(document.getElementById('compression-factor').value) || 0;
            document.getElementById('compression-label').innerText = `${comp > 0 ? '+' : ''}${comp}%`;

            const data = {
                gL: parseFloat(document.getElementById('garment-l').value) || 0,
                gW: parseFloat(document.getElementById('garment-w').value) || 0,
                gH: parseFloat(document.getElementById('garment-h').value) || 0,
                uW: parseFloat(document.getElementById('unit-weight').value) || 0,
                oQty: parseInt(document.getElementById('order-qty').value) || 0,
                bL: parseFloat(document.getElementById('box-l').value) || 0,
                bW: parseFloat(document.getElementById('box-w').value) || 0,
                bH: parseFloat(document.getElementById('box-h').value) || 0,
                bTare: parseFloat(document.getElementById('box-tare').value) || 0,
                maxKg: parseFloat(document.getElementById('max-weight-limit').value) || 0,
                compressionPct: comp,
                lockFlat: document.getElementById('lock-flat-orientation').checked
            };

            const res = calculateMetrics(data);
            document.getElementById('res-orientation-badge').innerText = res.fitsInBox ? res.bestOri.oriName : 'Sin cabida';
            document.getElementById('res-recommended-pcs').innerText = `${res.recommendedPcs} PCS`;
            document.getElementById('res-binding-constraint').innerText = res.bindingConstraintText;
            document.getElementById('res-grid-dims').innerText = `${res.grid.cols} × ${res.grid.rows} × ${res.grid.layers} (${res.spatialMax} máx)`;
            document.getElementById('res-weights-combined').innerText = `${(res.netWeightGrams/1000).toFixed(2)} kg / ${res.grossWeightKg.toFixed(2)} kg`;
            document.getElementById('res-efficiency').innerText = `${res.efficiency}%`;
            document.getElementById('res-total-cartons-cbm').innerText = `${res.totalCartons.toLocaleString()} cajas (${res.totalOrderCBM.toFixed(2)} m³)`;
            document.getElementById('res-clearance-text').innerText = `L: ${res.clearance.l}" | W: ${res.clearance.w}" | H: ${res.clearance.h}"`;

            const warn = document.getElementById('calculator-warning-box');
            if (!res.fitsInBox || res.weightConstrained) {
                warn.classList.remove('hidden');
                document.getElementById('calculator-warning-text').innerText = res.bindingConstraintText;
            } else {
                warn.classList.add('hidden');
            }
        }

        function autoOptimizeBox() {
            const brandId = document.getElementById('brand-select').value;
            let candidates = masterBoxes.filter(b => b.brandId === brandId);
            if (candidates.length === 0) candidates = masterBoxes;

            const gL = parseFloat(document.getElementById('garment-l').value) || 0;
            const gW = parseFloat(document.getElementById('garment-w').value) || 0;
            const gH = parseFloat(document.getElementById('garment-h').value) || 0;
            const uW = parseFloat(document.getElementById('unit-weight').value) || 0;
            const oQty = parseInt(document.getElementById('order-qty').value) || 1;
            const compressionPct = parseFloat(document.getElementById('compression-factor').value) || 0;
            const lockFlat = document.getElementById('lock-flat-orientation').checked;

            let bestBox = null, bestEff = -1;
            candidates.forEach(box => {
                const m = calculateMetrics({
                    gL, gW, gH, uW, oQty,
                    bL: box.l, bW: box.w, bH: box.h, bTare: box.tare, maxKg: box.maxKg,
                    compressionPct, lockFlat
                });
                if (m.recommendedPcs > 0 && parseFloat(m.efficiency) > bestEff) {
                    bestEff = parseFloat(m.efficiency);
                    bestBox = box;
                }
            });

            if (bestBox) {
                document.getElementById('box-catalog-select').value = bestBox.id;
                onBoxCatalogChange();
                showToast(`Caja optimizada: ${bestBox.name} (${bestEff}%)`, 'success');
            }
        }

        function searchExistingStyleInForm() {
            const q = (document.getElementById('lookup-existing-input')?.value || '').trim().toLowerCase();
            const container = document.getElementById('lookup-existing-results');
            if (!container) return;
            container.innerHTML = '';

            const list = q
                ? tests.filter(t => t.styleCode.toLowerCase().includes(q) || (t.season && t.season.toLowerCase().includes(q)))
                : tests.slice(0, 3);

            list.slice(0, 6).forEach(t => {
                const b = getBrand(t.brandId);
                const card = document.createElement('div');
                card.className = 'p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between text-xs';
                card.innerHTML = `
                    <div class="truncate pr-2">
                        <span class="font-mono text-[10px] font-bold bg-slate-800 text-white px-1.5 py-0.5 rounded mr-1">${t.season || 'SP26'}</span>
                        <span class="font-bold text-slate-800">${t.styleCode}</span>
                        <p class="text-[10px] text-slate-500 mt-0.5">${b.name} &bull; ${t.garmentL}"×${t.garmentW}"×${t.garmentH}"</p>
                    </div>
                    <div class="flex space-x-1 flex-shrink-0">
                        <button type="button" onclick="cloneMcqRecord('${t.id}')" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 px-2 py-1 rounded-lg font-bold text-[11px]" title="Crear nuevo MCQ usando esta plantilla">
                            Clonar
                        </button>
                        <button type="button" onclick="editMcqRecord('${t.id}')" class="bg-white hover:bg-red-50 text-red-600 border border-slate-300 px-2 py-1 rounded-lg font-bold text-[11px]" title="Editar este registro">
                            Editar
                        </button>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        function openNewMcqClean() {
            switchView('create-mcq');
            document.getElementById('mcq-form').reset();
            document.getElementById('test-id').value = '';
            document.getElementById('form-mcq-title').innerText = 'Registrar Nuevo MCQ Oficial';
            onBrandChange();
        }

        function editMcqRecord(testId) {
            const t = tests.find(x => x.id === testId);
            if (!t) return;
            registerRecentSearch(testId);
            switchView('create-mcq');

            document.getElementById('test-id').value = t.id;
            document.getElementById('form-mcq-title').innerText = `Editando MCQ: ${t.styleCode}`;
            document.getElementById('season-select').value = t.season || 'SP26';
            document.getElementById('brand-select').value = t.brandId;
            onBrandChange();

            document.getElementById('style-code').value = t.styleCode;
            document.getElementById('garment-category').value = t.category;
            document.getElementById('garment-l').value = t.garmentL;
            document.getElementById('garment-w').value = t.garmentW;
            document.getElementById('garment-h').value = t.garmentH;
            document.getElementById('compression-factor').value = t.compressionPct || 0;
            document.getElementById('lock-flat-orientation').checked = !!t.lockFlat;
            document.getElementById('unit-weight').value = t.unitWeightGrams;
            document.getElementById('order-qty').value = t.orderQty;

            const matchingBox = masterBoxes.find(b => b.name === t.boxName && b.brandId === t.brandId);
            document.getElementById('box-catalog-select').value = matchingBox ? matchingBox.id : 'custom';
            document.getElementById('box-l').value = t.boxL;
            document.getElementById('box-w').value = t.boxW;
            document.getElementById('box-h').value = t.boxH;
            document.getElementById('box-tare').value = t.boxTareGrams;
            document.getElementById('max-weight-limit').value = t.maxWeightLimitKg;
            runLiveCalculation();
            showToast(`Estilo "${t.styleCode}" cargado en el formulario`, 'info');
        }

        function cloneMcqRecord(testId) {
            const t = tests.find(x => x.id === testId);
            if (!t) return;
            registerRecentSearch(testId);
            switchView('create-mcq');

            // Dejamos test-id vacío para que se guarde como un registro NUEVO en Supabase
            document.getElementById('test-id').value = '';
            document.getElementById('form-mcq-title').innerText = `Clonando Plantilla: ${t.styleCode}`;
            document.getElementById('season-select').value = t.season || 'SP26';
            document.getElementById('brand-select').value = t.brandId;
            onBrandChange();

            document.getElementById('style-code').value = t.styleCode;
            document.getElementById('garment-category').value = t.category;
            document.getElementById('garment-l').value = t.garmentL;
            document.getElementById('garment-w').value = t.garmentW;
            document.getElementById('garment-h').value = t.garmentH;
            document.getElementById('compression-factor').value = t.compressionPct || 0;
            document.getElementById('lock-flat-orientation').checked = !!t.lockFlat;
            document.getElementById('unit-weight').value = t.unitWeightGrams;
            document.getElementById('order-qty').value = t.orderQty;

            const matchingBox = masterBoxes.find(b => b.name === t.boxName && b.brandId === t.brandId);
            document.getElementById('box-catalog-select').value = matchingBox ? matchingBox.id : 'custom';
            document.getElementById('box-l').value = t.boxL;
            document.getElementById('box-w').value = t.boxW;
            document.getElementById('box-h').value = t.boxH;
            document.getElementById('box-tare').value = t.boxTareGrams;
            document.getElementById('max-weight-limit').value = t.maxWeightLimitKg;
            runLiveCalculation();

            // Resaltar el selector de temporada para que el usuario elija la nueva temporada
            const seasonSelect = document.getElementById('season-select');
            seasonSelect.focus();
            showToast(`Plantilla cargada. Elige la nueva temporada y presiona Guardar`, 'success');
        }

        async function handleMcqFormSubmit(event) {
            event.preventDefault();
            const testId = document.getElementById('test-id').value;
            const boxSelectEl = document.getElementById('box-catalog-select');
            const selectedBoxName = (boxSelectEl.value && boxSelectEl.value !== 'custom')
                ? boxSelectEl.options[boxSelectEl.selectedIndex]?.text
                : 'Caja Personalizada';

            const record = {
                id: testId ? testId : null,
                season: document.getElementById('season-select').value,
                brandId: document.getElementById('brand-select').value,
                styleCode: document.getElementById('style-code').value.trim(),
                category: document.getElementById('garment-category').value,
                garmentL: parseFloat(document.getElementById('garment-l').value),
                garmentW: parseFloat(document.getElementById('garment-w').value),
                garmentH: parseFloat(document.getElementById('garment-h').value),
                compressionPct: parseFloat(document.getElementById('compression-factor').value) || 0,
                lockFlat: document.getElementById('lock-flat-orientation').checked,
                unitWeightGrams: parseFloat(document.getElementById('unit-weight').value),
                orderQty: parseInt(document.getElementById('order-qty').value),
                boxName: selectedBoxName,
                boxL: parseFloat(document.getElementById('box-l').value),
                boxW: parseFloat(document.getElementById('box-w').value),
                boxH: parseFloat(document.getElementById('box-h').value),
                boxTareGrams: parseFloat(document.getElementById('box-tare').value),
                maxWeightLimitKg: parseFloat(document.getElementById('max-weight-limit').value),
                createdBy: currentUser ? currentUser.name : 'Sistema'
            };

            try {
                const res = await apiRequest('/api/mcq/tests', 'POST', record);
                await loadAllData();
                registerRecentSearch(res.id);
                showToast(testId ? 'MCQ actualizado en Supabase' : `Nuevo MCQ guardado en temporada ${record.season}`, 'success');
                switchView('dashboard');
            } catch (err) {
                showToast('Error al guardar el MCQ en Supabase', 'info');
            }
        }

        async function deleteTest(id) {
            if (!currentUser || currentUser.role !== 'ADMIN') {
                showToast('Solo los administradores pueden eliminar MCQs', 'info');
                return;
            }
            if (confirm('¿Confirmas que deseas eliminar este MCQ de Supabase?')) {
                try {
                    await apiRequest(`/api/mcq/tests/${id}`, 'DELETE');
                    recentSearchedIds = recentSearchedIds.filter(x => x !== id);
                    saveAllData();
                    await loadAllData();
                    refreshAllViews();
                    showToast('Registro MCQ eliminado de Supabase', 'success');
                } catch (err) {
                    showToast('No se pudo eliminar el registro', 'info');
                }
            }
        }

        // ==========================================
        // VISTA 4: SIMULADOR 3D CONECTADO
        // ==========================================
        function populateSimulatorMcqDropdown() {
            const sel = document.getElementById('sim-load-mcq');
            if (!sel) return;
            sel.innerHTML = '<option value="">-- Simulación Libre / Personalizada --</option>';
            tests.forEach(t => {
                sel.innerHTML += `<option value="${t.id}">[${t.season || 'SP26'}] ${t.styleCode}</option>`;
            });
        }

        function loadMcqIntoSimulator(testId) {
            const t = tests.find(x => x.id === testId);
            if (!t) return;
            registerRecentSearch(t.id);
            document.getElementById('sim-g-l').value = t.garmentL;
            document.getElementById('sim-g-w').value = t.garmentW;
            document.getElementById('sim-g-h').value = t.garmentH;
            document.getElementById('sim-u-w').value = t.unitWeightGrams;
            document.getElementById('sim-comp').value = t.compressionPct || 0;
            document.getElementById('sim-b-l').value = t.boxL;
            document.getElementById('sim-b-w').value = t.boxW;
            document.getElementById('sim-b-h').value = t.boxH;
            document.getElementById('sim-b-tare').value = t.boxTareGrams;
            document.getElementById('sim-max-kg').value = t.maxWeightLimitKg;
            document.getElementById('sim-lock-flat').checked = !!t.lockFlat;
            updateEmbeddedSimulator();
        }

        function openMcqDirectlyInSimulator(testId) {
            switchView('simulator');
            setTimeout(() => {
                document.getElementById('sim-load-mcq').value = testId;
                loadMcqIntoSimulator(testId);
            }, 80);
        }

        function openFormInEmbeddedSimulator() {
            const gL = parseFloat(document.getElementById('garment-l').value) || 14;
            const gW = parseFloat(document.getElementById('garment-w').value) || 10;
            const gH = parseFloat(document.getElementById('garment-h').value) || 1.2;
            const uW = parseFloat(document.getElementById('unit-weight').value) || 650;
            const comp = parseFloat(document.getElementById('compression-factor').value) || 0;
            const bL = parseFloat(document.getElementById('box-l').value) || 24;
            const bW = parseFloat(document.getElementById('box-w').value) || 16;
            const bH = parseFloat(document.getElementById('box-h').value) || 18;
            const bTare = parseFloat(document.getElementById('box-tare').value) || 900;
            const maxKg = parseFloat(document.getElementById('max-weight-limit').value) || 16;
            const lockFlat = document.getElementById('lock-flat-orientation').checked;

            switchView('simulator');
            setTimeout(() => {
                document.getElementById('sim-g-l').value = gL;
                document.getElementById('sim-g-w').value = gW;
                document.getElementById('sim-g-h').value = gH;
                document.getElementById('sim-u-w').value = uW;
                document.getElementById('sim-comp').value = comp;
                document.getElementById('sim-b-l').value = bL;
                document.getElementById('sim-b-w').value = bW;
                document.getElementById('sim-b-h').value = bH;
                document.getElementById('sim-b-tare').value = bTare;
                document.getElementById('sim-max-kg').value = maxKg;
                document.getElementById('sim-lock-flat').checked = lockFlat;
                updateEmbeddedSimulator();
            }, 80);
        }

        function transferSimToCreateMcq() {
            const gL = document.getElementById('sim-g-l').value;
            const gW = document.getElementById('sim-g-w').value;
            const gH = document.getElementById('sim-g-h').value;
            const uW = document.getElementById('sim-u-w').value;
            const comp = document.getElementById('sim-comp').value;
            const bL = document.getElementById('sim-b-l').value;
            const bW = document.getElementById('sim-b-w').value;
            const bH = document.getElementById('sim-b-h').value;
            const bTare = document.getElementById('sim-b-tare').value;
            const maxKg = document.getElementById('sim-max-kg').value;
            const lockFlat = document.getElementById('sim-lock-flat').checked;

            switchView('create-mcq');
            document.getElementById('garment-l').value = gL;
            document.getElementById('garment-w').value = gW;
            document.getElementById('garment-h').value = gH;
            document.getElementById('unit-weight').value = uW;
            document.getElementById('compression-factor').value = comp;
            document.getElementById('box-catalog-select').value = 'custom';
            document.getElementById('box-l').value = bL;
            document.getElementById('box-w').value = bW;
            document.getElementById('box-h').value = bH;
            document.getElementById('box-tare').value = bTare;
            document.getElementById('max-weight-limit').value = maxKg;
            document.getElementById('lock-flat-orientation').checked = lockFlat;
            runLiveCalculation();
            showToast('Medidas del simulador 3D cargadas para guardar el MCQ', 'success');
        }

        function initEmbedded3D() {
            const container = document.getElementById('embedded-3d-container');
            if (!container) return;
            if (animationFrameId) cancelAnimationFrame(animationFrameId);
            container.innerHTML = '';

            scene = new THREE.Scene();
            scene.background = new THREE.Color(0x090d16);
            scene.add(new THREE.GridHelper(60, 30, 0x334155, 0x1e293b));

            const w = container.clientWidth || 700;
            const h = container.clientHeight || 460;
            camera = new THREE.PerspectiveCamera(45, w / h, 0.1, 1000);
            renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setSize(w, h);
            renderer.setPixelRatio(window.devicePixelRatio);
            container.appendChild(renderer.domElement);

            controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;

            scene.add(new THREE.AmbientLight(0xffffff, 0.75));
            const dirLight = new THREE.DirectionalLight(0xffffff, 0.85);
            dirLight.position.set(25, 45, 25);
            scene.add(dirLight);

            updateEmbeddedSimulator();

            function animate() {
                animationFrameId = requestAnimationFrame(animate);
                controls.update();
                renderer.render(scene, camera);
            }
            animate();
        }

        function updateEmbeddedSimulator() {
            if (!scene) return;
            const toRemove = [];
            scene.traverse(obj => {
                if (obj.userData && obj.userData.isSimObject) toRemove.push(obj);
            });
            toRemove.forEach(obj => scene.remove(obj));
            simGarmentMeshes = [];

            const data = {
                gL: parseFloat(document.getElementById('sim-g-l').value) || 14,
                gW: parseFloat(document.getElementById('sim-g-w').value) || 10,
                gH: parseFloat(document.getElementById('sim-g-h').value) || 1.2,
                uW: parseFloat(document.getElementById('sim-u-w').value) || 700,
                oQty: 1000,
                bL: parseFloat(document.getElementById('sim-b-l').value) || 24,
                bW: parseFloat(document.getElementById('sim-b-w').value) || 16,
                bH: parseFloat(document.getElementById('sim-b-h').value) || 18,
                bTare: parseFloat(document.getElementById('sim-b-tare').value) || 900,
                maxKg: parseFloat(document.getElementById('sim-max-kg').value) || 16,
                compressionPct: parseFloat(document.getElementById('sim-comp').value) || 0,
                lockFlat: document.getElementById('sim-lock-flat').checked
            };

            const m = calculateMetrics(data);
            document.getElementById('sim-pcs-badge').innerText = `${m.recommendedPcs} PCS`;
            document.getElementById('sim-res-ori').innerText = m.fitsInBox ? m.bestOri.oriName : 'Sin cabida';
            document.getElementById('sim-res-grid').innerText = `${m.grid.cols} × ${m.grid.rows} × ${m.grid.layers}`;
            document.getElementById('sim-res-weight').innerText = `${m.grossWeightKg.toFixed(2)} kg`;
            document.getElementById('sim-res-eff').innerText = `${m.efficiency}%`;
            document.getElementById('sim-res-clear').innerText = `${m.clearance.l}" / ${m.clearance.w}" / ${m.clearance.h}"`;

            function getErgonomicStatus(grossKg, maxKg, weightConstrained) {
            const limit = maxKg || 16.0;
            const ratio = limit > 0 ? (grossKg / limit) : 0;

            if (weightConstrained || ratio >= 0.95) {
                return {
                    badgeClass: 'bg-red-100 text-red-800 border-red-300',
                    dotClass: 'bg-red-600',
                    label: weightConstrained ? 'Tope Ergo' : 'Al Límite',
                    icon: 'fa-triangle-exclamation text-red-600'
                };
            } else if (ratio >= 0.80) {
                return {
                    badgeClass: 'bg-amber-100 text-amber-800 border-amber-300',
                    dotClass: 'bg-amber-500',
                    label: 'Carga Alta',
                    icon: 'fa-scale-unbalanced text-amber-600'
                };
            }
            return {
                badgeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                dotClass: 'bg-emerald-500',
                label: 'Óptimo',
                icon: 'fa-check-circle text-emerald-600'
            };
        }

        function getEfficiencyBadgeClass(effValue) {
            const eff = parseFloat(effValue) || 0;
            if (eff >= 75) return 'bg-emerald-50 text-emerald-700 border border-emerald-200';
            if (eff >= 55) return 'bg-blue-50 text-blue-700 border border-blue-200';
            return 'bg-amber-50 text-amber-700 border border-amber-200';
        }

            const slider = document.getElementById('sim-layer-slider');
            const maxLayers = Math.max(1, m.grid.layers);
            slider.max = maxLayers;
            slider.value = maxLayers;
            document.getElementById('sim-layer-label').innerText = `Todas (${maxLayers})`;

            const boxGeo = new THREE.BoxGeometry(data.bL, data.bH, data.bW);
            const boxMat = new THREE.MeshPhysicalMaterial({
                color: 0xef4444, transparent: true, opacity: 0.12, side: THREE.DoubleSide, depthWrite: false
            });
            const boxMesh = new THREE.Mesh(boxGeo, boxMat);
            boxMesh.position.set(0, data.bH / 2, 0);
            boxMesh.userData = { isSimObject: true };
            scene.add(boxMesh);

            const wire = new THREE.LineSegments(
                new THREE.EdgesGeometry(boxGeo),
                new THREE.LineBasicMaterial({ color: 0xf87171 })
            );
            wire.position.copy(boxMesh.position);
            wire.userData = { isSimObject: true };
            scene.add(wire);

            if (m.fitsInBox && m.recommendedPcs > 0) {
                const gDims = m.bestOri;
                const gGeo = new THREE.BoxGeometry(gDims.l * 0.96, gDims.h * 0.92, gDims.w * 0.96);
                const eGeo = new THREE.EdgesGeometry(gGeo);
                const eMat = new THREE.LineBasicMaterial({ color: 0x0f172a });

                const startX = -data.bL / 2 + gDims.l / 2;
                const startY = gDims.h / 2;
                const startZ = -data.bW / 2 + gDims.w / 2;

                let count = 0;
                for (let l = 0; l < m.grid.layers; l++) {
                    for (let r = 0; r < m.grid.rows; r++) {
                        for (let c = 0; c < m.grid.cols; c++) {
                            if (count >= m.recommendedPcs) break;
                            const hue = (0.55 + (l * 0.07)) % 1.0;
                            const mat = new THREE.MeshStandardMaterial({
                                color: new THREE.Color().setHSL(hue, 0.8, 0.55),
                                roughness: 0.35,
                                wireframe: simWireframe
                            });
                            const mesh = new THREE.Mesh(gGeo, mat);
                            mesh.position.set(startX + c * gDims.l, startY + l * gDims.h, startZ + r * gDims.w);
                            mesh.userData = { isSimObject: true, layerIndex: l + 1 };
                            mesh.add(new THREE.LineSegments(eGeo, eMat));
                            scene.add(mesh);
                            simGarmentMeshes.push(mesh);
                            count++;
                        }
                    }
                }
            }

            controls.target.set(0, data.bH / 2, 0);
            camera.position.set(data.bL * 1.5, data.bH * 1.7, data.bW * 2.1);
            controls.update();
        }

        function updateSimLayerVisibility() {
            const slider = document.getElementById('sim-layer-slider');
            const val = parseInt(slider.value);
            const max = parseInt(slider.max);
            document.getElementById('sim-layer-label').innerText = val === max ? `Todas (${max})` : `1 a ${val}`;
            simGarmentMeshes.forEach(m => { m.visible = m.userData.layerIndex <= val; });
        }

        function toggleSimWireframe() {
            simWireframe = !simWireframe;
            simGarmentMeshes.forEach(m => { if (m.material) m.material.wireframe = simWireframe; });
        }

        // ==========================================
        // VISTA 5: PANEL DE ADMINISTRACIÓN
        // ==========================================
        function renderAdminPanel() {
            // 1. Panel de Usuarios
            const uList = document.getElementById('admin-users-list');
            if (uList) {
                uList.innerHTML = '';
                users.forEach(u => {
                    const div = document.createElement('div');
                    div.className = 'py-2.5 flex items-center justify-between';
                    div.innerHTML = `
                        <div>
                            <span class="font-bold text-slate-800">${u.name}</span>
                            <span class="text-slate-400 font-mono ml-1">(@${u.username})</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold ${u.role === 'ADMIN' ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'}">${u.role}</span>
                            ${u.username !== 'admin' ? `<button onclick="deleteUser('${u.username}')" class="text-slate-400 hover:text-red-600"><i class="fa-solid fa-trash"></i></button>` : ''}
                        </div>
                    `;
                    uList.appendChild(div);
                });
            }

            // 2. Panel de Marcas y Temporadas
            const bList = document.getElementById('admin-brands-list');
            if (bList) {
                bList.innerHTML = '';
                
                // Sección superior interna para administrar Temporadas activas
                let seasonsHtml = `
                    <div class="col-span-full bg-slate-50 p-3.5 rounded-xl border border-slate-200 mb-2 space-y-2">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                            <span class="font-bold text-slate-800 text-xs flex items-center space-x-1.5">
                                <i class="fa-solid fa-calendar-days text-red-600"></i>
                                <span>Temporadas Registradas</span>
                            </span>
                            <button onclick="promptAddSeason()" class="text-[11px] bg-red-600 hover:bg-red-700 text-white px-2.5 py-1 rounded-md font-bold transition">
                                + Agregar Temporada
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            ${seasons.map(s => `
                                <div class="inline-flex items-center bg-white border border-slate-200 px-2.5 py-1 rounded-lg text-xs font-mono font-bold text-slate-700 space-x-2 shadow-sm">
                                    <span>${s}</span>
                                    <button onclick="deleteSeasonCode('${s}')" class="text-slate-400 hover:text-red-600 transition" title="Eliminar Temporada">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;

                // Tarjetas de Marcas con sus Cajas Master
                let brandsCardsHtml = brands.map(b => {
                    const brandBoxes = masterBoxes.filter(x => x.brandId === b.id);
                    let boxesHtml = brandBoxes.length === 0 
                        ? `<p class="text-[11px] text-slate-400 italic">No hay cajas registradas</p>` 
                        : brandBoxes.map(box => `
                            <div class="flex items-center justify-between bg-white p-2 rounded-lg border border-slate-200 text-[11px]">
                                <span class="font-bold text-slate-700 truncate pr-2">${box.name}</span>
                                <button onclick="deleteMasterBox('${box.id}')" class="text-slate-400 hover:text-red-600 p-1 transition" title="Eliminar Caja">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        `).join('');

                    return `
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                <div class="flex items-center space-x-2 truncate pr-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black text-white flex-shrink-0" style="background:${b.color}">${b.badge}</span>
                                    <span class="font-bold text-slate-800 text-xs truncate">${b.name}</span>
                                </div>
                                <div class="flex items-center space-x-2 flex-shrink-0">
                                    <span class="text-[10px] text-slate-500 font-mono">${brandBoxes.length} cajas</span>
                                    <button onclick="deleteBrandItem('${b.id}')" class="text-slate-400 hover:text-red-600 p-1 transition" title="Eliminar Marca">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="space-y-1.5 max-h-36 overflow-y-auto">
                                ${boxesHtml}
                            </div>
                        </div>
                    `;
                }).join('');

                bList.innerHTML = seasonsHtml + brandsCardsHtml;
            }
        }

        async function deleteSeasonCode(code) {
            if (!confirm(`¿Estás seguro de eliminar la temporada ${code}? Se ocultarán sus registros asociados.`)) return;
            try {
                await apiRequest(`/api/mcq/seasons/${code}`, 'DELETE');
                await loadAllData();
                refreshAllViews();
                showToast(`Temporada ${code} eliminada`, 'success');
            } catch (err) {
                showToast('Error al eliminar la temporada', 'info');
            }
        }

        async function deleteBrandItem(brandId) {
            if (!confirm(`¿Estás seguro de eliminar esta marca? Se eliminarán también sus cajas master asociadas.`)) return;
            try {
                await apiRequest(`/api/mcq/brands/${brandId}`, 'DELETE');
                await loadAllData();
                populateFormSelects();
                refreshAllViews();
                showToast('Marca eliminada correctamente', 'success');
            } catch (err) {
                showToast('Error al eliminar la marca', 'info');
            }
        }

        // ==========================================
        // MODALES AUXILIARES Y EXPORTACIÓN
        // ==========================================
        function openNewBrandModal() { document.getElementById('new-brand-modal').classList.remove('hidden'); }
        function closeNewBrandModal() { document.getElementById('new-brand-modal').classList.add('hidden'); }

        async function handleNewBrandSubmit(event) {
            event.preventDefault();
            const name = document.getElementById('new-brand-name').value.trim();
            const badge = document.getElementById('new-brand-badge').value.trim().toUpperCase();
            const color = document.getElementById('new-brand-color').value;
            const newId = name.toLowerCase().replace(/[^a-z0-9]/g, '-');
            if (brands.some(b => b.id === newId)) return showToast('La marca ya existe', 'info');

            try {
                await apiRequest('/api/mcq/brands', 'POST', { id: newId, name, badge, color });
                await loadAllData();
                populateFormSelects();
                refreshAllViews();
                closeNewBrandModal();
                showToast(`Marca ${name} registrada en Supabase`, 'success');
            } catch (err) {
                showToast('Error al guardar la marca', 'info');
            }
        }

        function openNewBoxModal() { document.getElementById('box-catalog-modal').classList.remove('hidden'); }
        function closeNewBoxModal() { document.getElementById('box-catalog-modal').classList.add('hidden'); }

        async function handleNewBoxSubmit(event) {
            event.preventDefault();
            const brandId = document.getElementById('brand-select').value;
            const name = document.getElementById('new-box-name').value.trim();
            const l = parseFloat(document.getElementById('new-box-l').value);
            const w = parseFloat(document.getElementById('new-box-w').value);
            const h = parseFloat(document.getElementById('new-box-h').value);
            const tare = parseFloat(document.getElementById('new-box-tare').value);
            const maxKg = parseFloat(document.getElementById('new-box-max').value);

            try {
                await apiRequest('/api/mcq/boxes', 'POST', { brandId, name, l, w, h, tare, maxKg });
                await loadAllData();
                closeNewBoxModal();
                populateBoxCatalogDropdown(name);
                showToast('Caja agregada al catálogo en Supabase', 'success');
            } catch (err) {
                showToast('Error al guardar la caja', 'info');
            }
        }

        function openDetailModal(testId) {
            const t = tests.find(x => x.id === testId);
            if (!t) return;
            currentActiveTestId = testId;
            const brand = getBrand(t.brandId);
            const m = calculateMetrics({
                gL: t.garmentL, gW: t.garmentW, gH: t.garmentH,
                uW: t.unitWeightGrams, oQty: t.orderQty,
                bL: t.boxL, bW: t.boxW, bH: t.boxH,
                bTare: t.boxTareGrams, maxKg: t.maxWeightLimitKg,
                compressionPct: t.compressionPct || 0, lockFlat: !!t.lockFlat
            });

            document.getElementById('detail-brand-badge').style.backgroundColor = brand.color;
            document.getElementById('detail-brand-badge').innerText = brand.badge;
            document.getElementById('detail-title').innerText = `${t.styleCode}`;
            document.getElementById('detail-subtitle').innerText = `Temporada ${t.season || 'SP26'} • ${brand.name} • ID: ${t.id}`;

            document.getElementById('detail-content').innerHTML = `
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-red-50 p-4 rounded-xl border border-red-200">
                        <span class="text-[10px] font-bold uppercase text-red-700 block">MCQ Oficial Aprobado</span>
                        <h4 class="text-3xl font-black text-red-600 font-mono mt-1">${m.recommendedPcs} PCS / Caja</h4>
                        <p class="text-xs text-slate-600 mt-1">Acomodo: <strong>${m.grid.cols} × ${m.grid.rows} × ${m.grid.layers}</strong> (${m.bestOri.oriName})</p>
                        <p class="text-xs text-slate-600">Holgura libre: L:${m.clearance.l}" | W:${m.clearance.w}" | H:${m.clearance.h}"</p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-1 text-xs">
                        <div class="flex justify-between"><span class="text-slate-500">Temporada:</span><span class="font-bold font-mono">${t.season || 'SP26'}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Peso Bruto Caja:</span><span class="font-bold font-mono">${m.grossWeightKg.toFixed(2)} kg (Máx ${t.maxWeightLimitKg} kg)</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Eficiencia Caja:</span><span class="font-bold font-mono text-blue-600">${m.efficiency}%</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Total Cajas (${t.orderQty} pcs):</span><span class="font-bold font-mono">${m.totalCartons} CTNS (${m.totalOrderCBM.toFixed(2)} m³)</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Registrado por:</span><span class="font-semibold">${t.createdBy || 'Admin'}</span></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 text-xs bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                    <div>
                        <strong class="block text-slate-700">Prenda Doblada</strong>
                        <span>${t.garmentL}" × ${t.garmentW}" × ${t.garmentH}" (${t.unitWeightGrams} g)</span>
                    </div>
                    <div>
                        <strong class="block text-slate-700">${t.boxName}</strong>
                        <span>${t.boxL}" × ${t.boxW}" × ${t.boxH}" (Tara: ${t.boxTareGrams} g)</span>
                    </div>
                </div>
                <!-- Contenedor de la captura 3D en la Ficha -->
                <div class="bg-slate-950 p-3 rounded-xl border border-slate-800 text-center space-y-2">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Visualización 3D del Acomodo en Caja</p>
                    <div id="modal-3d-snapshot-container" class="w-full h-56 flex items-center justify-center overflow-hidden rounded-lg bg-slate-900 border border-slate-800">
                        <span class="text-xs text-slate-500">Generando captura 3D...</span>
                    </div>
                </div>
            `;
            document.getElementById('detail-modal').classList.remove('hidden');

            // Renderizar la miniatura 3D de forma oculta o instantánea para la ficha
            setTimeout(() => generateSnapshotForModal(t), 50);
        }

        function closeDetailModal() { document.getElementById('detail-modal').classList.add('hidden'); }

        function openDetailInSimulator() {
            closeDetailModal();
            if (currentActiveTestId) openMcqDirectlyInSimulator(currentActiveTestId);
        }

        function copySummaryToClipboard() {
            const t = tests.find(x => x.id === currentActiveTestId);
            if (!t) return;
            const brand = getBrand(t.brandId);
            const m = calculateMetrics({
                gL: t.garmentL, gW: t.garmentW, gH: t.garmentH,
                uW: t.unitWeightGrams, oQty: t.orderQty,
                bL: t.boxL, bW: t.boxW, bH: t.boxH,
                bTare: t.boxTareGrams, maxKg: t.maxWeightLimitKg,
                compressionPct: t.compressionPct || 0, lockFlat: !!t.lockFlat
            });
            const txt = `FICHA MCQ [${t.season || 'SP26'}] - ${brand.name.toUpperCase()}\nEstilo: ${t.styleCode}\nMCQ: ${m.recommendedPcs} PCS/Caja (${m.grid.cols}x${m.grid.rows}x${m.grid.layers})\nCaja: ${t.boxName} (${t.boxL}"x${t.boxW}"x${t.boxH}")\nPeso Bruto: ${m.grossWeightKg.toFixed(2)} kg | Eficiencia: ${m.efficiency}%`;
            navigator.clipboard.writeText(txt).then(() => showToast('Ficha copiada al portapapeles', 'success'));
        }

        function exportToCSV() {
            const headers = ['ID', 'Temporada', 'Marca', 'Estilo', 'Categoria', 'Prenda_L', 'Prenda_W', 'Prenda_H', 'Peso_g', 'Caja', 'MCQ_PCS', 'Peso_Bruto_kg', 'Eficiencia_Pct', 'CBM_Total'];
            const rows = tests.map(t => {
                const b = getBrand(t.brandId);
                const m = calculateMetrics({
                    gL: t.garmentL, gW: t.garmentW, gH: t.garmentH,
                    uW: t.unitWeightGrams, oQty: t.orderQty,
                    bL: t.boxL, bW: t.boxW, bH: t.boxH,
                    bTare: t.boxTareGrams, maxKg: t.maxWeightLimitKg,
                    compressionPct: t.compressionPct || 0, lockFlat: !!t.lockFlat
                });
                return [t.id, t.season || 'SP26', `"${b.name}"`, `"${t.styleCode.replace(/"/g, '""')}"`, `"${t.category}"`, t.garmentL, t.garmentW, t.garmentH, t.unitWeightGrams, `"${t.boxName}"`, m.recommendedPcs, m.grossWeightKg.toFixed(2), m.efficiency, m.totalOrderCBM.toFixed(2)].join(',');
            });
            const blob = new Blob(['\uFEFF' + headers.join(',') + '\n' + rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = `MCQ_Global_Seasons_${new Date().toISOString().slice(0,10)}.csv`;
            a.click();
            showToast('Archivo Excel/CSV descargado', 'success');
        }

        function exportAllData() {
            const backup = { users, seasons, brands, masterBoxes, tests, exportDate: new Date().toISOString() };
            const a = document.createElement('a');
            a.href = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(backup, null, 2));
            a.download = `mcq_portal_backup_${new Date().toISOString().slice(0,10)}.json`;
            a.click();
            showToast('Respaldo JSON descargado', 'success');
        }

        function importAllData(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = async (e) => {
                try {
                    const data = JSON.parse(e.target.result);
                    if (data.tests && Array.isArray(data.tests)) {
                        for (const t of data.tests) {
                            await apiRequest('/api/mcq/tests', 'POST', t);
                        }
                    }
                    await loadAllData();
                    populateFormSelects();
                    refreshAllViews();
                    showToast('Datos restaurados en Supabase con éxito', 'success');
                } catch (err) {
                    showToast('Archivo JSON inválido o error al importar', 'info');
                }
            };
            reader.readAsText(file);
        }

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `${type === 'success' ? 'bg-emerald-600' : 'bg-slate-900'} text-white px-4 py-3 rounded-xl shadow-xl flex items-center space-x-2.5 text-xs font-semibold border border-slate-700`;
            toast.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-circle-check' : 'fa-circle-info text-amber-400'}"></i><span>${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        function generateSnapshotForModal(t) {
            const snapContainer = document.getElementById('modal-3d-snapshot-container');
            if (!snapContainer) return;

            // Creamos un canvas temporal de Three.js para renderizar la miniatura del modelo
            const width = snapContainer.clientWidth || 500;
            const height = snapContainer.clientHeight || 220;

            const tempScene = new THREE.Scene();
            tempScene.background = new THREE.Color(0x090d16);

            const tempCamera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
            const tempRenderer = new THREE.WebGLRenderer({ antialias: true, preserveDrawingBuffer: true });
            tempRenderer.setSize(width, height);

            tempScene.add(new THREE.AmbientLight(0xffffff, 0.75));
            const dirLight = new THREE.DirectionalLight(0xffffff, 0.85);
            dirLight.position.set(25, 45, 25);
            tempScene.add(dirLight);

            const m = calculateMetrics({
                gL: t.garmentL, gW: t.garmentW, gH: t.garmentH,
                uW: t.unitWeightGrams, oQty: t.orderQty,
                bL: t.boxL, bW: t.boxW, bH: t.boxH,
                bTare: t.boxTareGrams, maxKg: t.maxWeightLimitKg,
                compressionPct: t.compressionPct || 0, lockFlat: !!t.lockFlat
            });

            // Dibujar caja transparente
            const boxGeo = new THREE.BoxGeometry(t.boxL, t.boxH, t.boxW);
            const boxMat = new THREE.MeshPhysicalMaterial({ color: 0xef4444, transparent: true, opacity: 0.15, side: THREE.DoubleSide, depthWrite: false });
            const boxMesh = new THREE.Mesh(boxGeo, boxMat);
            boxMesh.position.set(0, t.boxH / 2, 0);
            tempScene.add(boxMesh);

            const wire = new THREE.LineSegments(new THREE.EdgesGeometry(boxGeo), new THREE.LineBasicMaterial({ color: 0xf87171 }));
            wire.position.copy(boxMesh.position);
            tempScene.add(wire);

            // Dibujar prendas interiores del acomodo óptimo
            if (m.fitsInBox && m.recommendedPcs > 0) {
                const gDims = m.bestOri;
                const gGeo = new THREE.BoxGeometry(gDims.l * 0.96, gDims.h * 0.92, gDims.w * 0.96);
                const eGeo = new THREE.EdgesGeometry(gGeo);
                const eMat = new THREE.LineBasicMaterial({ color: 0x0f172a });

                const startX = -t.boxL / 2 + gDims.l / 2;
                const startY = gDims.h / 2;
                const startZ = -t.boxW / 2 + gDims.w / 2;

                let count = 0;
                for (let l = 0; l < m.grid.layers; l++) {
                    for (let r = 0; r < m.grid.rows; r++) {
                        for (let c = 0; c < m.grid.cols; c++) {
                            if (count >= m.recommendedPcs) break;
                            const hue = (0.55 + (l * 0.07)) % 1.0;
                            const mat = new THREE.MeshStandardMaterial({ color: new THREE.Color().setHSL(hue, 0.8, 0.55), roughness: 0.35 });
                            const mesh = new THREE.Mesh(gGeo, mat);
                            mesh.position.set(startX + c * gDims.l, startY + l * gDims.h, startZ + r * gDims.w);
                            mesh.add(new THREE.LineSegments(eGeo, eMat));
                            tempScene.add(mesh);
                            count++;
                        }
                    }
                }
            }

            tempCamera.position.set(t.boxL * 1.6, t.boxH * 1.8, t.boxW * 2.2);
            tempCamera.lookAt(0, t.boxH / 2, 0);
            tempRenderer.render(tempScene, tempCamera);

            // Convertir render a imagen e incrustarla en el contenedor del modal
            const dataURL = tempRenderer.domElement.toDataURL('image/png');
            snapContainer.innerHTML = `<img src="${dataURL}" class="mx-auto h-full object-contain rounded-lg shadow-inner" alt="Simulación 3D MCQ">`;
            
            // Liberar memoria del renderer temporal
            tempRenderer.dispose();
        }

    </script>

<footer class="rc-footer">
  <div>
    <span>Desarrollado por <strong style="color: #F8FAFC;">Digital RogCla Studio</strong></span>
    <span style="margin: 0 8px; opacity: 0.4;">|</span>
    <span style="font-style: italic;">Software a medida que impulsa tu negocio</span>
  </div>
  <div style="font-family: 'JetBrains Mono', monospace; font-size: 0.78rem;">
    <span style="color: #2563EB;">&lt;</span><strong style="color: #F8FAFC;">RC</strong><span style="color: #2563EB;">/&gt;</span> &copy; {{ date('Y') }}
  </div>
</footer>

</body>
</html>
