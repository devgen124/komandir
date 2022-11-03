jQuery(function($){
    var geobase_parent = jQuery('#base_name');
    var geobase_sypexgeo_fields = jQuery('.sypexgeo-settings');
    var geobase_dadata_fields = jQuery('.dadata-settings');
    var geobase_maxmind_fields = jQuery('.maxmind-settings');
    geobase_parent.change(function(){
        geobase_name = jQuery('#base_name').val();

       if (geobase_name == 'dadata_service'){
            geobase_sypexgeo_fields.toggleClass( 'hide-if-js', true );
            geobase_dadata_fields.toggleClass( 'hide-if-js', false );
            geobase_maxmind_fields.toggleClass( 'hide-if-js', true );
        }else if(geobase_name == 'sypexgeo_service'){
            geobase_sypexgeo_fields.toggleClass( 'hide-if-js', false );
            geobase_dadata_fields.toggleClass( 'hide-if-js', true );
            geobase_maxmind_fields.toggleClass( 'hide-if-js', true );
        }else if(geobase_name == 'maxmind_service'){
            geobase_sypexgeo_fields.toggleClass( 'hide-if-js', true );
            geobase_dadata_fields.toggleClass( 'hide-if-js', true );
            geobase_maxmind_fields.toggleClass( 'hide-if-js', false );
        }else{
           geobase_sypexgeo_fields.toggleClass( 'hide-if-js', true );
           geobase_dadata_fields.toggleClass( 'hide-if-js', true );
           geobase_maxmind_fields.toggleClass( 'hide-if-js', true );
       }
    });
});