@extends('admin.index')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="p-4 bg-blue-50 text-blue-600 rounded-lg"><i class="fas fa-user-tie text-2xl"></i></div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Employés</p>
                <p class="text-2xl font-bold text-gray-800">142</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="p-4 bg-yellow-50 text-yellow-600 rounded-lg"><i class="fas fa-folder-open text-2xl"></i></div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Dossiers Incomplets</p>
                <p class="text-2xl font-bold text-gray-800">28</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="p-4 bg-green-50 text-green-600 rounded-lg"><i class="fas fa-file-pdf text-2xl"></i></div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Dossiers Fusionnés</p>
                <p class="text-2xl font-bold text-gray-800">114</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Dossier de l'employé</h3>
                <p class="text-sm text-gray-500">Nom et Prenom : NDONGO Jean-Baptiste | Matricule : 784512-M</p>
            </div>
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 shadow-sm">
                <i class="fas fa-layer-group"></i> Fusionner le dossier unique
            </button>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                <div class="border border-gray-200 rounded-lg p-4 flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="text-sm font-semibold text-gray-800">Fiche de renseignement</h4>
                        <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded-full">Existant</span>
                    </div>
                    <button class="mt-4 text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1">
                        <i class="fas fa-eye"></i> Voir la pièce
                    </button>
                </div>

                <div class="border border-gray-200 rounded-lg p-4 flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="text-sm font-semibold text-gray-800">Photocopie de la CNI</h4>
                        <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded-full">Existant</span>
                    </div>
                    <button class="mt-4 text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1">
                        <i class="fas fa-eye"></i> Voir la pièce
                    </button>
                </div>

                <div class="border border-dashed border-gray-300 bg-gray-50 rounded-lg p-4 flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="text-sm font-semibold text-gray-600">Récépissé COPPE</h4>
                        <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded-full">Manquant</span>
                    </div>
                    <label class="mt-4 cursor-pointer text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1">
                        <i class="fas fa-upload"></i> Ajouter une pièce
                        <input type="file" class="hidden" accept=".pdf">
                    </label>
                </div>

                <div class="border border-dashed border-gray-300 bg-gray-50 rounded-lg p-4 flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="text-sm font-semibold text-gray-600">Acte d’intégration ou contrat</h4>
                        <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded-full">Manquant</span>
                    </div>
                    <label class="mt-4 cursor-pointer text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1">
                        <i class="fas fa-upload"></i> Ajouter une pièce
                        <input type="file" class="hidden" accept=".pdf">
                    </label>
                </div>

            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-800">Liste du personnel</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-medium">Matricule</th>
                        <th class="px-6 py-4 font-medium">Nom et Prénom</th>
                        <th class="px-6 py-4 font-medium">Progression Dossier</th>
                        <th class="px-6 py-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-200">
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900">784512-M</td>
                        <td class="px-6 py-4 text-gray-700">NDONGO Jean-Baptiste</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-yellow-400 h-2 rounded-full" style="width: 80%"></div>
                                </div>
                                <span class="text-xs text-gray-500">14/18</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button class="text-blue-600 hover:text-blue-800 font-medium">Gérer le dossier</button>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900">129443-A</td>
                        <td class="px-6 py-4 text-gray-700">AWONO Marie-Claire</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: 100%"></div>
                                </div>
                                <span class="text-xs text-gray-500">18/18</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button class="text-blue-600 hover:text-blue-800 font-medium">Gérer le dossier</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
