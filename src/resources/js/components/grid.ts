import Masonry from 'masonry-layout';
import InfiniteScroll from 'infinite-scroll';
import imagesLoaded from 'imagesloaded';

interface PostData {
    title: string;
    s3_key: string;
    view_count: number;
    created_at: string;
    post_url: string;
}

interface ApiResponse {
    posts: PostData[];
    total: number;
}

interface GridHandlerOptions {
    tagId?: string;
}

export class GridLayoutHandler {
    private readonly container: HTMLElement;
    private readonly loadingSpinner: HTMLElement;
    private readonly endOfContentMessage: HTMLElement;
    private masonryInstance: Masonry | null = null;
    private infScroll: InfiniteScroll | null = null;
    private buttons: NodeListOf<HTMLButtonElement>;
    private readonly tagId?: string;

    private sort: string = 'newest';
    private readonly sortSelect: HTMLSelectElement;

    constructor(options: GridHandlerOptions = {}) {
        this.container = document.getElementById('postsGrid') as HTMLElement;
        this.loadingSpinner = document.getElementById('loadingSpinner') as HTMLElement;
        this.endOfContentMessage = document.getElementById('endOfContent') as HTMLElement;

        this.buttons   = document.querySelectorAll('.btn-view');
        this.tagId = options.tagId;

        this.sortSelect = document.getElementById('sortSelect') as HTMLSelectElement;
        this.sort = this.sortSelect.value;

        this.setGridView();
        this.initInfiniteScroll();
        this.initViewToggleButtons();
        this.initSortChange();
    }

    /**
     * Infinite Scroll: path='?page={{#}}', append=false, DOM append with load event
     */
    private initInfiniteScroll(): void {
        if (this.infScroll) {
            this.infScroll.destroy();
            this.infScroll = null;
        }

        const pathBase: string = this.buildPath();

        this.infScroll = new InfiniteScroll(this.container, {
            path: pathBase,
            responseBody: 'json',
            history: false,
            append: false,
            outlayer: undefined,
        });

        this.infScroll.on('request', (): void => {
            this.loadingSpinner.style.visibility = 'show';
            this.endOfContentMessage.style.display = 'none';
        });

        // Convert JSON to card HTML that is received at the load event → appendItems() → Reposition if Masonry exists
        this.infScroll.on('load', (data: ApiResponse): void => {
            this.loadingSpinner.style.visibility = 'hidden';
            if (!data.posts || !data.posts.length) {
                this.endOfContentMessage.style.display = 'block';
                this.infScroll?.destroy();
                return;
            }

            // Card HTML with .invisible class
            const itemsHTML: string = data.posts.map((post: PostData): string => `
            <div class="grid-item invisible" data-id="${post.s3_key}">
              <div class="card bg-gray">
                <a href="${post.post_url}" class="text-decoration-none">
                  <img src="/api/images/${post.s3_key}"
                       class="card-img-top card-img-cover-300"
                       alt="${post.title}">
                  <div class="card-body p-2">
                    <div class="card-title">
                      <h3 class="fs-6 text-light p-2">${post.title}</h3>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                      <small class="muted-text">
                        <i class="fas fa-eye"></i>
                        ${new Intl.NumberFormat().format(post.view_count)}
                      </small>
                      <small class="muted-text">
                        ${post.created_at}
                      </small>
                    </div>
                  </div>
                </a>
              </div>
            </div>
          `).join('');

            const tempDiv: HTMLDivElement = document.createElement('div');
            tempDiv.innerHTML = itemsHTML;
            const newItems: NodeListOf<HTMLDivElement> = tempDiv.querySelectorAll('.grid-item');

            imagesLoaded(newItems, (): void => {
                this.infScroll?.appendItems(newItems);
                if (this.masonryInstance) {
                    // @ts-ignore
                    this.masonryInstance.appended(Array.from(newItems));
                    // @ts-ignore
                    this.masonryInstance.layout();
                }

                // invisible → visible
                newItems.forEach((item: HTMLDivElement): void => item.classList.remove('invisible'));
            });
        });
    }

    private initSortChange(): void {
        if (!this.sortSelect) return;

        this.sortSelect.addEventListener('change', (): void => {
            this.sort = this.sortSelect!.value;

            const items: NodeListOf<Element> = this.container.querySelectorAll('.grid-item');
            items.forEach(item => item.remove());

            if (this.infScroll) {
                this.infScroll.destroy();
                this.infScroll = null;
            }

            if (this.masonryInstance) {
                // @ts-ignore
                this.masonryInstance.destroy();
                this.masonryInstance = null;
            }

            this.initInfiniteScroll();

            if (this.container.classList.contains('waterfall-view')) {
                this.setWaterfallView();
            }

            if (this.infScroll) {
                (this.infScroll as any).pageIndex = 0;
                // @ts-ignore
                this.infScroll.loadNextPage();
            }
        });
    }

    private buildPath(): string {
        let path: string = `/api/posts?sort=${this.sort}&page={{#}}`;
        if (this.tagId) {
            path = `/api/posts?sort=${this.sort}&tag=${this.tagId}&page={{#}}`;
        }
        return path;
    }

    private initViewToggleButtons(): void {
        this.buttons.forEach((btn): void => {
            btn.addEventListener('click', (): void => {
                this.buttons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const viewType: string | undefined = btn.dataset.view;
                if (viewType === 'waterfall') {
                    this.setWaterfallView();
                } else {
                    this.setGridView();
                }
            });
        });
    }

    private setGridView(): void {
        this.container.classList.add('grid-view');
        this.container.classList.remove('waterfall-view');

        if (this.masonryInstance) {
            // @ts-ignore
            this.masonryInstance.destroy();
            this.masonryInstance = null;
        }
    }

    private setWaterfallView(): void {
        this.container.classList.remove('grid-view');
        this.container.classList.add('waterfall-view');

        if (!this.masonryInstance) {
            imagesLoaded(this.container, (): void => {
                this.masonryInstance = new Masonry(this.container, {
                    itemSelector: '.grid-item',
                    columnWidth: '.grid-sizer',
                    gutter: 16,
                    horizontalOrder: true,
                    percentPosition: true,
                    fitWidth: true,
                });

                // Re-register outlayer to Infinite Scroll
                if (this.infScroll) {
                    (this.infScroll as any).options.outlayer = this.masonryInstance;
                }
            });
        } else {
            // @ts-ignore
            this.masonryInstance.layout();
        }
    }
}
