@role('Comptable|Caissier')
    @include('layouts.partials.sidebars.comptabilite')
@elserole('Secretaire')
    @include('layouts.partials.sidebars.secretaire')
@elserole('Directeur Agence')
    @include('layouts.partials.sidebars.agences')
@else
    @include('layouts.partials.sidebars.direction')
@endrole
