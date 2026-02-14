document.querySelectorAll( '.wp-block-beer-list-beverage-list' ).forEach( ( list ) => {
	const filterBar = list.querySelector( '.beverage-list__filters' );
	const searchInput = list.querySelector( '.beverage-list__search-input' );
	const items = list.querySelectorAll( '.beverage-list__item' );

	let activeType = '';
	let searchTerm = '';

	function applyFilters() {
		const term = searchTerm.toLowerCase();

		items.forEach( ( item ) => {
			const matchesType = ! activeType || item.dataset.types.split( ' ' ).includes( activeType );
			const matchesSearch = ! term || item.textContent.toLowerCase().includes( term );

			item.hidden = ! ( matchesType && matchesSearch );
		} );
	}

	if ( filterBar ) {
		const buttons = filterBar.querySelectorAll( '.beverage-list__filter' );

		buttons.forEach( ( btn ) => {
			btn.addEventListener( 'click', () => {
				activeType = btn.dataset.type;

				buttons.forEach( ( b ) => b.classList.remove( 'is-active' ) );
				btn.classList.add( 'is-active' );

				applyFilters();
			} );
		} );
	}

	if ( searchInput ) {
		searchInput.addEventListener( 'input', () => {
			searchTerm = searchInput.value;
			applyFilters();
		} );
	}
} );
