import '@uppy/core/dist/style.css'
import '@uppy/dashboard/dist/style.css'
import 'bootstrap';
import Uppy, {UppyFile} from '@uppy/core'
import Dashboard from '@uppy/dashboard'
import XHRUpload from '@uppy/xhr-upload'

interface FileMeta {
    title: string;
    description: string;
    [key: string]: string | number | boolean | undefined; // Allow any other properties
}
interface ServerResponse {
    post_url: string;
    delete_url: string;
    [key: string]: string | number | boolean | undefined;
}

interface UploadResponse {
    body?: ServerResponse;
    status: number;
    bytesUploaded?: number;
    uploadURL?: string;
}

class ImageUploader {
    private uppy!: Uppy<FileMeta, ServerResponse>;

    // Form elements
    private submitButton: HTMLButtonElement;
    private cancelButton: HTMLButtonElement;
    private postDetailsForm: HTMLFormElement;
    private titleInput: HTMLInputElement;
    private uploadStatus: HTMLElement;
    private spinner: HTMLElement;
    private statusText: HTMLElement;

    // Tag selection
    private tagListItems: NodeListOf<HTMLElement>;
    private selectedTagsContainer: HTMLElement;
    private selectedTagIds: number[] = [];

    // State flags
    private postUrl: string = '';
    private hasFile: boolean = false;
    private hasTitle: boolean = false;

    constructor() {
        this.submitButton = document.getElementById('submitButton') as HTMLButtonElement;
        this.cancelButton = document.getElementById('cancelButton') as HTMLButtonElement;
        this.postDetailsForm = document.getElementById('postDetailsForm') as HTMLFormElement;
        this.titleInput = document.getElementById('title') as HTMLInputElement;
        this.uploadStatus = document.getElementById('uploadStatus') as HTMLElement;
        this.spinner = document.getElementById('spinner') as HTMLElement;
        this.statusText = document.getElementById('statusText') as HTMLElement;

        this.tagListItems = document.querySelectorAll('#tagsDropdown .tag-list-item') as NodeListOf<HTMLElement>;
        this.selectedTagsContainer = document.getElementById('selectedTagsContainer') as HTMLElement;

        // Initialize
        this.initializeUppy();
        this.setupUppyEvents();
        this.setupFormEvents();
        this.setupTagUI();
    }

    private initializeUppy(): void {
        this.uppy = new Uppy<FileMeta, ServerResponse>({
            debug: true,
            autoProceed: false,
            restrictions: {
                maxFileSize: 20 * 1024 * 1024, // For jpeg/png files, max size is 10MB (handled below)
                minNumberOfFiles: 1,
                maxNumberOfFiles: 1,
                allowedFileTypes: ['.jpg', '.jpeg', '.png', '.gif']
            }
        })
            .use(Dashboard, {
                inline: true,
                target: '#uppy',
                height: 400,
                width: '100%',
                theme: 'dark',
                showProgressDetails: true,
                hideUploadButton: true,
                hideRetryButton: true,
                hidePauseResumeButton: true,
                proudlyDisplayPoweredByUppy: false,
                locale: {
                    strings: {
                        dropPasteFiles: 'Drop image here or %{browseFiles}',
                        browseFiles: 'browse file',
                    },
                    pluralize: (n: number): 1|0 => {
                        if (n === 1) {
                            return 0;
                        }
                        return 1;
                    }
                },
            })
            .use(XHRUpload, {
                endpoint: '/api/posts/create',
                formData: true, // Convert uppy.setMeta() object to form data (multipart/form-data)
                fieldName: 'userfile' // Single file upload ($_FILES['userfile'])
            });
    }

    private setupUppyEvents(): void {
        this.uppy
            .on('file-added', (file: UppyFile<FileMeta, ServerResponse>): void => {
                const fileSize: number = file.size ?? 0;
                if ((file.type === 'image/jpeg' || file.type === 'image/png') && fileSize > 10 * 1024 * 1024) {
                    this.uppy.removeFile(file.id);
                    this.uppy.info({
                        message: `${file.type === 'image/jpeg' ? 'JPEG' : 'PNG'} files must be under 10MB`,
                    });
                    return;
                }

                this.hasFile = true;
                this.updateSubmitButton();
            })
            .on('file-removed', (): void => {
                this.hasFile = false;
                this.updateSubmitButton();
            })
            .on('upload-start', (): void => {
                this.updateStatus('Preparing to upload...');
            })
            .on('progress', (progress: number): void => {
                if (progress > 0) {
                    this.updateStatus(`Uploading... ${Math.round(progress)}%`);
                }
            })
            .on('upload-success', (file: UppyFile<FileMeta, ServerResponse> | undefined, response: UploadResponse): void => {
                this.updateStatus(file?.name + ' was successfully uploaded!');
                this.postUrl = response.body?.post_url ?? '/';

                setTimeout((): void => {
                    this.updateStatus(this.generateSuccessMessage(response), true);
                    this.setupSuccessEventListeners();
                }, 1000);
            })
            .on('upload-error', (file: UppyFile<FileMeta, ServerResponse> | undefined, error: Error): void => {
                this.uploadStatus.classList.add('d-none');
                this.uppy.info({
                    message: 'Failed to upload: ' + file?.name + '. Error: ' + error.message,
                });
                this.submitButton.disabled = false;
            });
    }

    private setupFormEvents(): void {
        this.titleInput.addEventListener('input', (): void => {
            const title: string = this.titleInput.value.trim();
            this.hasTitle = (title.length > 0 && title.length <= 100);
            this.updateSubmitButton();
        });

        this.cancelButton.addEventListener('click', (): void => {
            window.location.href = '/';
        });

        this.postDetailsForm.addEventListener('submit', (e: SubmitEvent): void => {
            e.preventDefault();
            const title: string = this.titleInput.value.trim();
            const descriptionElement = document.getElementById('description') as HTMLTextAreaElement;
            const description: string = descriptionElement.value.trim();
            // Convert the tag array to a string "1,2,3"
            const tagsString: string = this.selectedTagIds.join(',');
            // Attach the meta data
            this.uppy.setMeta({
                title,
                description,
                tags: tagsString
            });

            this.submitButton.disabled = true;
            this.uploadStatus.classList.remove('d-none');
            this.uppy.upload(); // Promise returned by upload() is ignored since we're using the 'upload-success' event
        });
    }

    private setupTagUI(): void {
        this.tagListItems.forEach(item => {
            item.addEventListener('click', (): void => {
                const tagIdStr: string = item.getAttribute('data-tag-id') || '';
                const tagName: string = item.getAttribute('data-tag-name') || '';
                const tagId: number = parseInt(tagIdStr, 10);

                if (!isNaN(tagId) && !this.selectedTagIds.includes(tagId)) {
                    this.selectedTagIds.push(tagId);
                    const badge: HTMLSpanElement = document.createElement('span');
                    badge.className = 'badge bg-secondary d-flex align-items-center me-1 mb-1';
                    badge.style.cursor = 'pointer';
                    badge.innerHTML = `
                        ${tagName}
                        <i class="fas fa-times ms-1" style="font-size: 0.8em;"></i>
                    `;

                    badge.addEventListener('click', (): void => {
                        this.selectedTagsContainer.removeChild(badge);
                        this.selectedTagIds = this.selectedTagIds.filter(id => id !== tagId);
                    });

                    this.selectedTagsContainer.appendChild(badge);
                }
            });
        });
    }

    private updateSubmitButton(): void {
        this.submitButton.disabled = !(this.hasFile && this.hasTitle);
    }

    private updateStatus(content: string, isHtml: boolean = false): void {
        if (isHtml) {
            this.spinner.classList.add('d-none');
            this.statusText.innerHTML = content;
        } else {
            this.spinner.classList.remove('d-none');
            this.statusText.textContent = content;
        }
    }

    private setupSuccessEventListeners(): void {
        const copyButton = document.getElementById('copyButton') as HTMLButtonElement;
        copyButton.addEventListener('click', (): void => {
            const deleteUrl = document.getElementById('deleteUrl') as HTMLInputElement;

            try {
                navigator.clipboard.writeText(deleteUrl.value);
                const originalContent: string = copyButton.innerHTML;
                copyButton.innerHTML = '<i class="fas fa-check"></i> Copied!';
                setTimeout((): void => {
                    copyButton.innerHTML = originalContent;
                }, 2000);
            } catch (error) {
                console.error('Failed to copy:', error);
            }
        });

        const proceedButton = document.getElementById('proceedButton') as HTMLButtonElement;
        proceedButton.addEventListener('click', (): void => {
            window.location.href = this.postUrl;
        });
    }

    private generateSuccessMessage(response: UploadResponse): string {
        return `
          <h5 class="mb-4">Your image has been successfully uploaded!</h5>
    
          <div class="alert alert-warning d-flex align-items-center mb-4 text-start">
            <i class="fas fa-exclamation-triangle me-3"></i>
            <div>
              Save this URL to delete your image in the future.<br>
              This URL will not be shown again.
            </div>
          </div>
    
          <div class="input-group mb-4" style="max-width: 400px; margin: 0 auto;">
            <input type="text"
                   class="form-control bg-dark text-light border-secondary text-center user-select-none"
                   value="http://localhost:8080${response.body?.delete_url}"
                   id="deleteUrl"
                   readonly
                   style="cursor: default;"
            >
            <button class="btn btn-outline-light"
                    id="copyButton"
                    type="button">
              <i class="fas fa-clipboard"></i>
              Copy
            </button>
          </div>
    
          <button type="button"
                  class="btn btn-success px-4"
                  id="proceedButton">
            Got it!
          </button>
        `;
    }
}
document.addEventListener('DOMContentLoaded', (): void => {
    new ImageUploader();
});