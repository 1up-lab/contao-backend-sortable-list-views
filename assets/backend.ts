import { SortableListView } from './SortableListView'

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll<HTMLElement>('table.tl_listing tbody').forEach((el) => {
        new SortableListView(el).init()
    })
})
