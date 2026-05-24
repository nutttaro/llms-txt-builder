( function () {
	var el              = wp.element.createElement;
	var registerPlugin  = wp.plugins.registerPlugin;
	var ToggleControl   = wp.components.ToggleControl;
	var useSelect       = wp.data.useSelect;
	var useDispatch     = wp.data.useDispatch;
	var __              = wp.i18n.__;

	// WP 6.6+ moved PluginDocumentSettingPanel to wp.editor; fall back for older versions.
	var PluginDocumentSettingPanel = ( wp.editor && wp.editor.PluginDocumentSettingPanel )
		|| ( wp.editPost && wp.editPost.PluginDocumentSettingPanel );

	if ( ! PluginDocumentSettingPanel ) {
		return;
	}

	function LLMsTxtPanel() {
		var meta = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		}, [] );

		var editPost = useDispatch( 'core/editor' ).editPost;

		var ignorePage = !! meta._ntllms_txt_builder_ignore_page;
		var clearCache = !! meta._ntllms_txt_builder_clear_cache;

		return el(
			PluginDocumentSettingPanel,
			{
				name:  'ntllms-txt-builder-panel',
				title: __( 'LLMs.txt Builder', 'nt-llms-txt-builder' ),
				className: 'ntllms-txt-builder-panel',
			},
			el( ToggleControl, {
				label:   __( 'Ignore in LLMs.txt', 'nt-llms-txt-builder' ),
				help:    ignorePage
					? __( 'This post is excluded from LLMs.txt output.', 'nt-llms-txt-builder' )
					: __( 'This post is included in LLMs.txt output.', 'nt-llms-txt-builder' ),
				checked: ignorePage,
				onChange: function ( value ) {
					editPost( { meta: { _ntllms_txt_builder_ignore_page: value } } );
				},
			} ),
			el( ToggleControl, {
				label:   __( 'Clear cache on update', 'nt-llms-txt-builder' ),
				help:    __( 'Clear the LLMs.txt cache whenever this post is saved.', 'nt-llms-txt-builder' ),
				checked: clearCache,
				onChange: function ( value ) {
					editPost( { meta: { _ntllms_txt_builder_clear_cache: value } } );
				},
			} )
		);
	}

	registerPlugin( 'ntllms-txt-builder', {
		icon:   'media-text',
		render: LLMsTxtPanel,
	} );
} )();
