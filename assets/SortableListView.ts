import Sortable from 'sortablejs'

declare global {
    interface SortableListViews {
        token: string
        path: string
    }

    interface Window {
        sortableListViews: SortableListViews
    }
}

export class SortableListView {
    private readonly container: HTMLElement
    private readonly elements: NodeListOf<HTMLElement>

    constructor(container: HTMLElement) {
        this.container = container
        this.elements = this.container.querySelectorAll('td:first-child')
    }

    init(): void {
        if (this.elements.length <= 1) {
            return
        }

        // Remove order groups from Contao
        this.container.querySelectorAll('table.tl_listing tbody tr:not(.toggle_select)').forEach((el) => {
            el.remove()
        })

        Sortable.create(this.container, {
            handle: 'td.tl_file_list:last-child a.drag.drag-handle',
            onEnd: function (/** Event */ event) {
                const item: HTMLElement = event.item
                const href: string | undefined = item.querySelectorAll('a')?.[0].href
                const id: string | undefined = href?.match(/id=(\d+)/)?.[1]
                const action: string | undefined = window.location.href.match(/do=([^&]+)/)?.[1]
                const requestUri: URL = new URL(window.location.href)
                const url: URL = new URL(requestUri.origin + window.sortableListViews.path)
                const formData: FormData = new FormData()

                formData.append('REQUEST_TOKEN', window.sortableListViews.token)
                formData.append('action', action ?? '')
                formData.append('id', id ?? 0)
                formData.append('oldIndex', event.oldIndex)
                formData.append('newIndex', event.newIndex)

                try {
                    fetch(url, {
                        method: 'POST',
                        body: formData,
                    }).then((): void => {})
                } catch (error) {
                    console.error('Error:', error)
                }
            },
        })
    }
}
