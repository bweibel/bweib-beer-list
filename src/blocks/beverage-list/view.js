document.querySelectorAll( '.wp-block-beer-list-beverage-list' ).forEach( ( list ) => {
	const filterBar = list.querySelector( '.beverage-list__filters' );
	const searchInput = list.querySelector( '.beverage-list__search-input' );
	const paginationContainer = list.querySelector( '.beverage-list__pagination' );
	const items = list.querySelectorAll( '.beverage-list__item' );

	let activeType = '';
	let searchTerm = '';
	let currentPage = 1;
	const perPage = paginationContainer
		? parseInt( paginationContainer.dataset.perPage, 10 ) || 6
		: 0;

	function getMatchedItems() {
		const term = searchTerm.toLowerCase();
		return Array.from( items ).filter( ( item ) => {
			const matchesType = ! activeType || item.dataset.types.split( ' ' ).includes( activeType );
			const matchesSearch = ! term || item.textContent.toLowerCase().includes( term );
			return matchesType && matchesSearch;
		} );
	}

	function createPageButton( label, onClick, { active = false, disabled = false } = {} ) {
		const btn = document.createElement( 'button' );
		btn.className = 'beverage-list__page-btn';
		btn.textContent = label;

		if ( active ) {
			btn.classList.add( 'is-active' );
		}
		if ( disabled ) {
			btn.classList.add( 'is-disabled' );
			btn.disabled = true;
		}

		btn.addEventListener( 'click', onClick );
		return btn;
	}

	function renderPagination( matchedCount ) {
		if ( ! paginationContainer ) {
			return;
		}

		paginationContainer.innerHTML = '';
		const totalPages = Math.ceil( matchedCount / perPage );

		if ( totalPages <= 1 ) {
			return;
		}

		const goToPage = ( page ) => {
			currentPage = page;
			applyAll();
		};

		paginationContainer.appendChild(
			createPageButton( '\u00AB', () => goToPage( currentPage - 1 ), { disabled: currentPage === 1 } )
		);

		for ( let i = 1; i <= totalPages; i++ ) {
			paginationContainer.appendChild(
				createPageButton( String( i ), () => goToPage( i ), { active: i === currentPage } )
			);
		}

		paginationContainer.appendChild(
			createPageButton( '\u00BB', () => goToPage( currentPage + 1 ), { disabled: currentPage === totalPages } )
		);
	}

	function applyAll() {
		const matched = getMatchedItems();

		items.forEach( ( item ) => {
			item.hidden = true;
		} );

		if ( paginationContainer && perPage > 0 ) {
			const start = ( currentPage - 1 ) * perPage;
			matched.slice( start, start + perPage ).forEach( ( item ) => {
				item.hidden = false;
			} );
			renderPagination( matched.length );
		} else {
			matched.forEach( ( item ) => {
				item.hidden = false;
			} );
		}
	}

	if ( filterBar ) {
		filterBar.querySelectorAll( '.beverage-list__filter' ).forEach( ( btn ) => {
			btn.addEventListener( 'click', () => {
				activeType = btn.dataset.type;
				currentPage = 1;

				filterBar.querySelectorAll( '.beverage-list__filter' ).forEach( ( b ) => b.classList.remove( 'is-active' ) );
				btn.classList.add( 'is-active' );

				applyAll();
			} );
		} );
	}

	if ( searchInput ) {
		searchInput.addEventListener( 'input', () => {
			searchTerm = searchInput.value;
			currentPage = 1;
			applyAll();
		} );
	}

	// Initial render — paginate on load if enabled.
	applyAll();
} );
