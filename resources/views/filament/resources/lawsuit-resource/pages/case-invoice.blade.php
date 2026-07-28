<x-filament-panels::page>
{{ $this->form }}
{{ $this->table }}



    <div class="filament-tables-component">
        <div class="filament-tables-container rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="w-full divide-y divide-gray-200 text-sm text-gray-900 text-center">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-4 font-medium text-gray-500 uppercase tracking-wider">Document</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  
                        <tr class="hover:bg-gray-50">
                          <td class="px-4 py-4">{{ $this->documentTitles }}</td>                      
                        </tr>
                  
                   
                </tbody>
            </table>
        </div>
    </div>

    

</x-filament-panels::page>
