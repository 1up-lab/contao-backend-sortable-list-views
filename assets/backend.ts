import Sortable from 'sortablejs'
import { SortableListView } from './SortableListView'

function initialize(): void {
    document.querySelectorAll<HTMLElement>('table.tl_listing tbody').forEach((el) => {
        if (Sortable.get(el) === undefined) {
            new SortableListView(el).init()
        }
    })
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize)
} else {
    initialize()
}

document.addEventListener('turbo:load', initialize)
