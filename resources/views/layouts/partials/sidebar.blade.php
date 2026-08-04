@role('Directeur Agence')
    @include('layouts.partials.sidebars.agences')
@elserole('Comptable|Caissier')
    @include('layouts.partials.sidebars.comptabilite')
@elserole('Secretaire')
    @include('layouts.partials.sidebars.secretaire')
@elserole('Collectrice')
    @include('layouts.partials.sidebars.collectrice')
@elserole('Commercial')
    @include('layouts.partials.sidebars.commercial')
@else
    @include('layouts.partials.sidebars.direction')
@endrole
