document.querySelectorAll( '.wp-block-beer-list-beverage-list' ).forEach( ( list ) => {
	const filterBar = list.querySelector( '.beverage-list__filters' );
	const searchInput = list.querySelector( '.beverage-list__search-input' );
	const pagination = list.querySelector( '.beverage-list__pagination' );
	const items = list.querySelectorAll( '.beverage-list__item' );

	let activeType = '';
	let searchTerm = '';
	let currentPage = 1;
	const perPage = pagination ? parseInt( pagination.dataset.perPage, 10 ) || 6 : 0;

	function getVisibleItems() {
		const term = searchTerm.toLowerCase();

		return Array.from( items ).filter( ( item ) => {
			const matchesType = ! activeType || item.dataset.types.split( ' ' ).includes( activeType );
			const matchesSearch = ! term || item.textContent.toLowerCase().includes( term );
			return matchesType && matchesSearch;
		} );
	}

	function renderPagination( totalItems ) {
		if ( ! pagination ) {
			return;
		}

		const totalPages = Math.ceil( totalItems / perPage );
		pagination.innerHTML = '';

		if ( totalPages <= 1 ) {
			return;
		}

		const prev = document.createElement( 'button' );
		prev.className = 'beverage-list__page-btn';
		prev.textContent = '\u00AB';
		prev.disabled = currentPage === 1;
		if ( currentPage === 1 ) {
			prev.classList.add( 'is-disabled' );
		}
		prev.addEventListener( 'click', () => {
			if ( currentPage > 1 ) {
				currentPage--;
				applyAll();
			}
		} );
		pagination.appendChild( prev );

		for ( let i = 1; i <= totalPages; i++ ) {
			const btn = document.createElement( 'button' );
			btn.className = 'beverage-list__page-btn';
			btn.textContent = i;
			if ( i === currentPage ) {
				btn.classList.add( 'is-active' );
			}
			btn.addEventListener( 'click', () => {
				currentPage = i;
				applyAll();
			} );
			pagination.appendChild( btn );
		}

		const next = document.createElement( 'button' );
		next.className = 'beverage-list__page-btn';
		next.textContent = '\u00BB';
		next.disabled = currentPage === totalPages;
		if ( currentPage === totalPages ) {
			next.classList.add( 'is-disabled' );
		}
		next.addEventListener( 'click', () => {
			if ( currentPage < totalPages ) {
				currentPage++;
				applyAll();
			}
		} );
		pagination.appendChild( next );
	}

	function applyAll() {
		const matched = getVisibleItems();

		// Hide everything first.
		items.forEach( ( item ) => {
			item.hidden = true;
		} );

		if ( pagination && perPage > 0 ) {
			const start = ( currentPage - 1 ) * perPage;
			const paged = matched.slice( start, start + perPage );

			paged.forEach( ( item ) => {
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
		const buttons = filterBar.querySelectorAll( '.beverage-list__filter' );

		buttons.forEach( ( btn ) => {
			btn.addEventListener( 'click', () => {
				activeType = btn.dataset.type;
				currentPage = 1;

				buttons.forEach( ( b ) => b.classList.remove( 'is-active' ) );
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
