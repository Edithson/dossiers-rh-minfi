<?php

use App\Models\User;
use App\Models\Piece;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $totalPieces = Piece::count();
        $users = User::with('pieces')->get();

        $totalUsers = $users->count();
        $completeDossiersCount = 0;
        $incompleteDossiersCount = 0;

        $usersNeedingAttention = collect();

        // Analyse de tous les dossiers pour les statistiques
        foreach ($users as $user) {
            $validatedPieces = 0;

            foreach ($user->pieces as $piece) {
                $files = json_decode($piece->pivot->file_paths, true);
                if (is_array($files) && count($files) > 0) {
                    $validatedPieces++;
                }
            }

            $percentage = $totalPieces > 0 ? round(($validatedPieces / $totalPieces) * 100) : 0;

            if ($percentage == 100) {
                $completeDossiersCount++;
            } else {
                $incompleteDossiersCount++;
                // On stocke ceux qui ont besoin d'attention (moins de 100%)
                $usersNeedingAttention->push([
                    'user' => $user,
                    'percentage' => $percentage,
                    'missing_count' => $totalPieces - $validatedPieces
                ]);
            }
        }

        // Trier les employés ayant le plus faible pourcentage et prendre les 5 pires
        $worstDossiers = $usersNeedingAttention->sortBy('percentage')->take(5);

        // Récupérer les 5 derniers employés enregistrés
        $recentUsers = User::latest()->take(5)->get();

        return [
            'totalUsers' => $totalUsers,
            'completeDossiersCount' => $completeDossiersCount,
            'incompleteDossiersCount' => $incompleteDossiersCount,
            'complianceRate' => $totalUsers > 0 ? round(($completeDossiersCount / $totalUsers) * 100) : 0,
            'worstDossiers' => $worstDossiers,
            'recentUsers' => $recentUsers,
        ];
    }
}; ?>

<div class="max-w-7xl mx-auto p-4 lg:p-8 space-y-6">

    <div class="bg-gradient-to-r from-blue-900 to-indigo-800 rounded-2xl p-6 md:p-10 shadow-lg text-white flex flex-col md:flex-row justify-between items-center gap-6">
        <div>
            <h1 class="text-3xl md:text-4xl font-black mb-2 tracking-tight">Vue d'ensemble RH</h1>
            <p class="text-blue-200 text-sm md:text-base">Gérez et suivez l'évolution des dossiers de votre personnel en temps réel.</p>
        </div>
        <div class="shrink-0 flex gap-3">
            <a href="{{ route('users') }}" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl font-medium backdrop-blur-sm transition-all text-sm flex items-center gap-2">
                <i class="fas fa-list"></i> Tous les employés
            </a>
            <a href="{{route("create-user")}}" class="px-5 py-2.5 bg-blue-500 hover:bg-blue-400 rounded-xl font-bold transition-all text-sm flex items-center gap-2 shadow-lg shadow-blue-500/30">
                <i class="fas fa-plus"></i> Nouveau Dossier
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md transition-shadow">
            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-2xl shrink-0">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Personnel</p>
                <p class="text-3xl font-black text-gray-800">{{ $totalUsers }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md transition-shadow">
            <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-2xl shrink-0">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="w-full">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Conformité</p>
                <div class="flex items-end gap-2">
                    <p class="text-3xl font-black text-gray-800">{{ $complianceRate }}%</p>
                    <p class="text-xs text-gray-400 mb-1.5 border-b border-dashed border-gray-300">globale</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md transition-shadow">
            <div class="w-14 h-14 bg-green-50 text-green-500 rounded-xl flex items-center justify-center text-2xl shrink-0">
                <i class="fas fa-shield-check"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Dossiers à jour</p>
                <p class="text-3xl font-black text-gray-800">{{ $completeDossiersCount }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md transition-shadow">
            <div class="w-14 h-14 bg-red-50 text-red-500 rounded-xl flex items-center justify-center text-2xl shrink-0">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Incomplets</p>
                <p class="text-3xl font-black text-gray-800">{{ $incompleteDossiersCount }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-bell text-amber-500"></i> Dossiers prioritaires à compléter
                </h3>
            </div>
            <div class="p-6 flex-1">
                @if($worstDossiers->count() > 0)
                    <div class="space-y-4">
                        @foreach($worstDossiers as $data)
                            <div class="flex items-center justify-between p-4 rounded-xl border {{ $data['percentage'] == 0 ? 'bg-red-50/30 border-red-100' : 'bg-gray-50 border-gray-100' }} transition-colors hover:shadow-sm">
                                <div>
                                    <h4 class="font-bold text-gray-800">{{ $data['user']->name }}</h4>
                                    <p class="text-xs text-gray-500 mt-0.5">Matricule: {{ $data['user']->matricule }}</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="text-right hidden sm:block">
                                        <p class="text-xs font-bold text-gray-800">{{ $data['percentage'] }}% complété</p>
                                        <p class="text-xs text-red-500">{{ $data['missing_count'] }} pièce(s) manquante(s)</p>
                                    </div>
                                    <a href="{{ route('show-user', $data['user']->id) }}" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-blue-600 hover:bg-blue-50 hover:border-blue-200 transition-all shrink-0" title="Voir le dossier">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="h-full flex flex-col items-center justify-center text-gray-400 py-10">
                        <i class="fas fa-check-circle text-5xl mb-3 text-green-200"></i>
                        <p class="font-medium text-gray-500">Aucun dossier incomplet.</p>
                        <p class="text-sm">Tout votre personnel est en règle !</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-clock text-blue-500"></i> Récemment embauchés
                </h3>
                <a href="{{ route('users') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline">Voir tout</a>
            </div>
            <div class="flex-1 p-0">
                @if($recentUsers->count() > 0)
                    <ul class="divide-y divide-gray-100">
                        @foreach($recentUsers as $recentUser)
                            <li class="p-4 sm:px-6 hover:bg-blue-50/30 transition-colors flex items-center justify-between group">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 border border-gray-300 flex items-center justify-center text-gray-600 font-bold shrink-0">
                                        {{ substr($recentUser->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800 group-hover:text-blue-700 transition-colors">{{ $recentUser->name }}</p>
                                        <p class="text-xs text-gray-500">Ajouté le {{ $recentUser->created_at->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('show-user', $recentUser->id) }}" class="p-2 text-gray-400 hover:text-blue-600" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('edit-user', $recentUser->id) }}" class="p-2 text-gray-400 hover:text-yellow-600" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="h-full flex flex-col items-center justify-center text-gray-400 py-10">
                        <i class="fas fa-user-slash text-5xl mb-3 text-gray-200"></i>
                        <p class="font-medium text-gray-500">Aucun employé enregistré.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
