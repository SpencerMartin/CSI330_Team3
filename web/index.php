<!DOCTYPE html>
<html>
<head>
	<title>Scrum Notes</title>
	<meta name='viewport' content='initial-scale=1'>
	<script src='https://code.jquery.com/jquery-2.2.1.min.js'></script>
	<script src='https://code.jquery.com/ui/1.11.4/jquery-ui.min.js'></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
	<script>
	var notes = {
		"create" : function(){
			// Sends a GET request to api.php?action=create and get ID back, creating a new note
			$.get( 'api.php', {
				'action' : 'create'
			} ).done( function whenDone( data_text ){
				try{
					// Convert text received from request into JavaScript object
					var data = JSON.parse( data_text );

					// Create note element with ID returned from database, and empty note text
					notes.show( data.id, "Todo", "" );
				}catch( e ){
					console.log( 'Could not parse data for notes.create', data_text );
					alert( 'Error parsing data. See console for details.' );
				}
			} );
		},
		"updateColumn" : function( note_element ){
			// Sends a GET request to api.php?action=update to change database record for note

			// Get note data
			var note = $( note_element );
			var note_id = note.data( 'id' );

			// Mark the note as updating
			note.data( 'status', 'updating' );

			// Send update to server, and mark as "updated" when done
			$.get( 'api.php', {
				'action' : 'updateColumn',
				'id' : note_id,
				'column' : note.closest( "ul" ).attr( "id" )
			} ).done( function whenDone( data_text ){
				note.data( 'status', 'updated' );
			} );
		},
		"updateText" : function( note_element ){
			// Sends a GET request to api.php?action=update to change database record for note

			// Get note data
			var note = $( note_element );
			var note_id = note.data( 'id' );
			var note_content = $( note.children( '.note' )[0] );

			// Mark the note as updating
			note.data( 'status', 'updating' );

			// Send update to server, and mark as "updated" when done
			$.get( 'api.php', {
				'action' : 'updateText',
				'id' : note_id,
				'content' : note_content.val()
			} ).done( function whenDone( data_text ){
				note.data( 'status', 'updated' );
			} );
		},
		"delete" : function( note_element ){
			// Sends a GET request to api.php?action=delete to change database record for note

			// Get note data
			var note = $( note_element );
			var note_id = note.data( 'id' );

			// Send delete request to server, and remove note from screen when done
			$.get( 'api.php', {
				'action' : 'delete',
				'id' : note_id
			} ).done( function whenDone( data_text ){
				note.remove();
			} );
		},
		"show" : function( id, column, content ){
			// Get list for column
			var parent_column = $( "#" + column );

			// Create note UI elements
			var note_wrapper = $( "<li id='note" + id + "' class='note-wrapper' data-id='" + id + "'></li>" );
			parent_column.append( note_wrapper );

			var note = $( "<textarea class='note' onkeyup='notes.updateText( this.parentNode );' onmousedrag='return false;'>" + content + "</textarea>" );
			note_wrapper.append( note );

			var note_delete = $( "<button class='note-delete' type='button' onclick='notes.delete( this.parentNode );'><i class='fa fa-ban'></i></button>" );
			note_wrapper.append( note_delete );
		},
		"list" : function(){
			// Send list request to server, and show each note on screen when done
			$.get( 'api.php', {
				'action' : 'list'
			} ).done( function whenDone( data_text ){
				try{
					// Convert response into JavaScript object
					var data = JSON.parse( data_text );

					// Object is array of notes, so loop through and show each one
					for( var i = 0; i < data.length; i++ ){
						var note = data[i];
						notes.show( note.id, note.column, note.content );
					}
				}catch( e ){
					console.log( 'Could not parse data for notes.list', data_text );
					alert( 'Error parsing data. See console for details.' );
				}
			} );
		},
		"watch" : function(){
			// Send list request to server, and sgiw each note on screen when done
			$.get( 'api.php', {
				'action' : 'list'
			} ).done( function whenDone( data_text ){
				try{
					// Convert response into JavaScript object
					var data = JSON.parse( data_text );

					// Object is array of notes, so loop through and show each one
					for( var i = 0; i < data.length; i++ ){
						var note = data[i];
						var display_note = $( '#note' + note.id );
						if( display_note.length == 1 ){
							// Update via AJAX currently isn't working anymore - not sure why
						}else{
							// Note does not exist on page, so show it
							notes.show( note.id, note.column, note.content );
						}
					}

					// Make notes draggable
					$( function(){
						$( "ul.droptrue" ).sortable(
							{
								connectWith: "ul",
								items: ".note-wrapper",
								stop: function updateColumn( item ){
									var note_id = $( item.toElement ).attr('id');
									note_element = $( '#' + note_id );
									notes.updateColumn( note_element );
								}
							}
						);
					} );
				}catch( e ){
					console.log( 'Could not parse data for notes.list', data_text );
					alert( 'Error parsing data. See console for details.' );
				}
			} );
		}
	}
	</script>
	<style>
	/* Styling from http://cssdeck.com/labs/css3-sticky-note */
	@import url(http://fonts.googleapis.com/css?family=Gloria+Hallelujah);
	body{
		background:url(cork.jpg);
	}
	.note-wrapper{
		box-sizing:border-box;
		min-height:200px;
		min-width:200px;
		margin:20px;
		padding:30px 20px 15px 20px;
		position:relative;
		z-index:1;
		background-color:#F9EFAF;
		background:-webkit-linear-gradient(#F9EFAF, #F7E98D);
		background:-moz-linear-gradient(#F9EFAF, #F7E98D);
		background:-o-linear-gradient(#F9EFAF, #F7E98D);
		background:-ms-linear-gradient(#F9EFAF, #F7E98D);
		background-repeat:no-repeat;
		background-position:center;
		background:url( pin.png ), linear-gradient(#F9EFAF, #F7E98D);
		background-repeat:no-repeat, no-repeat;
		background-position:center -1px, center;
		box-shadow:0 4px 6px rgba(0,0,0,0.1);
		box-shadow:1px 1px 5px black;
		list-style-type:none;
		overflow:hidden;
	}
	
	.note{
		background-color:transparent;
		outline:0px;
		font-size:18px;
		font-family:'Gloria Hallelujah', cursive, Lucida Handwriting, Lucida, Cambria, serif;
		width:100%;
		height:calc(200px - 45px);
		line-height:1.5;
		border:0;
		border-radius:3px;
		overflow:hidden;
		resize:none;
		z-index:1;
	}
	.category{
		font-size:20px;
		font-family:'Gloria Hallelujah', cursive, Lucida Handwriting, Lucida, Cambria, serif;
		text-align: center;
		padding:30px 20px 15px 20px;
		min-height:120px;
		min-width:200px;
		background-position:top;
		background:url( headernote.png ) no-repeat;
		background-size:contain;
		background-position:center center, center;
		overflow:hidden;
	}
	.note-delete{
		display:none;
		position:absolute;
		top:10px;
		right:10px;
		color:red;
		padding:2px;
		z-index:2;
		font-size:18px;
		cursor:pointer;
	}
	.note-wrapper:hover .note-delete{
		display:block;
	}
	button{
		font-size:20px;
		padding:4px 6px;
	}

	#Todo, #InProgress, #NeedsTesting, #Done {
		float:left;
		width:20%;
	}
	
	#Todo li, #InProgress li, #NeedsTesting li, #Done li {
		width:20%;
	}
	</style>
</head>
<body>
	<div style='text-align:center;'>
		<button type='button' id='add-note' onclick='notes.create();'><i class='fa fa-plus' style='color:green;'></i> Add note</button>
		<button type='button' id='watch-notes' onclick='window.location.reload( true );'><i class='fa fa-refresh'></i> Refresh</button>
	</div>
	<div id='notes' class="ui-widget-content">
		<ul id="Todo" class="droptrue">
			<li class="category">To Do</li>
		</ul>
		<ul id="InProgress" class="droptrue">
			<li class="category">In Progress</li>
		</ul>
		<ul id="NeedsTesting" class="droptrue">
			<li class="category">Needs Testing</li>
		</ul>
		<ul id="Done" class="droptrue">
			<li class="category">Done</li>
		</ul>
	</div><!-- #notes -->
	<script>
	setInterval( notes.watch, 1500 );
	</script>
</body>
</html>
