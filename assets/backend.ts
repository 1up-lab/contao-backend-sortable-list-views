import { SortableListView } from './SortableListView'

function initialize(): void {
    document.querySelectorAll<HTMLElement>('table.tl_listing tbody').forEach((el) => {
        new SortableListView(el).init()
    })
}

document.addEventListener('DOMContentLoaded', initialize)
document.addEventListener('turbo:load', initialize)
