document.querySelectorAll( '.beverage-list__filters' ).forEach( ( filterBar ) => {
	const list = filterBar.closest( '.wp-block-beer-list-beverage-list' );
	const items = list.querySelectorAll( '.beverage-list__item' );
	const buttons = filterBar.querySelectorAll( '.beverage-list__filter' );

	buttons.forEach( ( btn ) => {
		btn.addEventListener( 'click', () => {
			const type = btn.dataset.type;

			buttons.forEach( ( b ) => b.classList.remove( 'is-active' ) );
			btn.classList.add( 'is-active' );

			items.forEach( ( item ) => {
				if ( ! type || item.dataset.types.split( ' ' ).includes( type ) ) {
					item.hidden = false;
				} else {
					item.hidden = true;
				}
			} );
		} );
	} );
} );
