@role('Comptable|Caissier')
    @include('layouts.partials.sidebars.comptabilite')
@elserole('Secretaire')
    @include('layouts.partials.sidebars.secretaire')
@else
    @include('layouts.partials.sidebars.direction')
@endrole
