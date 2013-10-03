
// supersede the default MediaFrame.Post view
var oldMediaFrame = wp.media.view.MediaFrame.Post;
wp.media.view.MediaFrame.Post = oldMediaFrame.extend({
 
    initialize: function() {
        oldMediaFrame.prototype.initialize.apply( this, arguments );
        
        this.states.add([
            new wp.media.controller.Library({
				id:         'game-gallery',
				title:      wp.media.view.l10n.createGameGalleryTitle,
				priority:   40,
				toolbar:    'game-gallery',
				filterable: 'uploaded',
				multiple:   'add',
				editable:   false,

				library:  wp.media.query({
					type: 'game'
				})
			}),
			
			new wp.media.controller.GameGalleryEdit({
			    id: 'game-gallery-edit',
			    title: wp.media.view.l10n.editGameGalleryTitle,
			    toolbar: 'game-gallery-edit',
				library: this.options.selection,
				editing: true,
				menu:    'game-gallery'
			}),
			
			new wp.media.controller.GameGalleryAdd({
			    id: 'game-gallery-add',
			    title: wp.media.view.l10n.addToGameGallery,
			    toolbar: 'game-gallery-add',
			    menu:    'game-gallery'
			})
        ]);
    },
    
    bindHandlers: function() {
		oldMediaFrame.prototype.bindHandlers.apply( this, arguments );
		this.on('menu:create:game-gallery', this.createMenu, this);
		this.on('toolbar:create:game-gallery', this.createToolbar, this);
		this.on('menu:render:game-gallery', this.gameGalleryMenu, this);
		this.on('toolbar:render:game-gallery', this.gameGalleryToolbar, this);
		this.on('toolbar:render:game-gallery-edit', this.gameGalleryEditToolbar, this);
		this.on('toolbar:render:game-gallery-add', this.gameGalleryAddToolbar, this);
	},
	
	gameGalleryMenu: function( view ) {
		var lastState = this.lastState(),
			previous = lastState && lastState.id,
			frame = this;

		view.set({
			cancel: {
				text: wp.media.view.l10n.cancelGameGallery,
				priority: 20,
				click:    function() {
					if ( previous )
						frame.setState( previous );
					else
						frame.close();
				}
			},
			separateCancel: new wp.media.View({
				className: 'separator',
				priority: 40
			})
		});
	},
    
    gameGalleryToolbar: function( view ) {
		var controller = this;

		this.selectionStatusToolbar( view );

		view.set( 'game-gallery', {
			style:    'primary',
			text:     wp.media.view.l10n.newGameGalleryButton,
			priority: 60,
			requires: { selection: true },

			click: function() {
				var selection = controller.state().get('selection'),
					edit = controller.state('game-gallery-edit'),
					models = selection.where({ type: 'game' });

				edit.set( 'library', new wp.media.model.Selection( models, {
					props:    selection.props.toJSON(),
					multiple: true
				}) );

				this.controller.setState('game-gallery-edit');
			}
		});
	},
	
	gameGalleryEditToolbar: function() {
		this.toolbar.set( new wp.media.view.Toolbar({
			controller: this,
			items: {
				insert: {
					style:    'primary',
					text:     wp.media.view.l10n.insertGameGalleryButton,
					priority: 80,
					requires: { library: true },

					click: function() {
						var controller = this.controller,
							state = controller.state();

						controller.close();
						
						// insert in tinymce
						var ed, mce = typeof(tinymce) != 'undefined';
						if ( ! window.wpActiveEditor ) {
			                if ( mce && tinymce.activeEditor ) {
				                ed = tinymce.activeEditor;
				                window.wpActiveEditor = ed.id;
			                } else if ( !qt ) {
				                return false;
			                }
		                } else if ( mce ) {
			                if ( tinymce.activeEditor && (tinymce.activeEditor.id == 'mce_fullscreen' || tinymce.activeEditor.id == 'wp_mce_fullscreen') )
				                ed = tinymce.activeEditor;
			                else
				                ed = tinymce.get(window.wpActiveEditor);
		                }
						
						var shortcode = wp.media.gallery.shortcode( state.get('library') ).string();
						ed.execCommand('mceInsertContent', false, shortcode.replace('gallery', 'game'));

						controller.reset();
						controller.setState('upload');
					}
				}
			}
		}) );
	},

	gameGalleryAddToolbar: function() {
		this.toolbar.set( new wp.media.view.Toolbar({
			controller: this,
			items: {
				insert: {
					style:    'primary',
					text:     wp.media.view.l10n.addToGameGalleryButton,
					priority: 80,
					requires: { selection: true },

					click: function() {
						var controller = this.controller,
							state = controller.state(),
							edit = controller.state('game-gallery-edit');

						edit.get('library').add( state.get('selection').models );
						state.trigger('reset');
						controller.setState('game-gallery-edit');
					}
				}
			}
		}) );
	}
 
});

wp.media.controller.GameGalleryAdd = wp.media.controller.GalleryAdd.extend({
    initialize: function() {
		// If we haven't been provided a `library`, create a `Selection`.
		if ( ! this.get('library') )
			this.set( 'library', wp.media.query({ type: 'game' }) );

		wp.media.controller.GalleryAdd.prototype.initialize.apply( this, arguments );
	}
});

wp.media.controller.GameGalleryEdit = wp.media.controller.GalleryEdit.extend({

	initialize: function() {
		wp.media.controller.GalleryEdit.prototype.initialize.apply( this, arguments );
	},

	activate: function() {
		var library = this.get('library');

		// Limit the library to images only.
		library.props.set( 'type', 'game' );

		// Watch for uploaded attachments.
		this.get('library').observe( wp.Uploader.queue );

		this.frame.on( 'content:render:browse', this.gallerySettings, this );

		wp.media.controller.Library.prototype.activate.apply( this, arguments );
	}
});

