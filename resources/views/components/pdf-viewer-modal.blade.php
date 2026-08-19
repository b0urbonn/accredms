<!-- Continuous Scrollable PDF Viewer Extra-Large Modal Component -->
<div class="modal fade" id="pdfViewerModal" tabindex="-1" aria-labelledby="pdfViewerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-lg-down">
        <div class="modal-content shadow-lg border-0" style="height: 92vh;">
            <!-- Modal Header -->
            <div class="modal-header bg-apple-dark text-white py-2 px-3 align-items-center">
                <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                    <i class="bi bi-file-earmark-pdf fs-4 text-warning"></i>
                    <div class="overflow-hidden">
                        <h6 class="modal-title mb-0 fw-bold text-truncate" id="pdfViewerTitle" style="max-width: 380px;">Document Viewer</h6>
                        <small class="text-light opacity-75 text-truncate d-block" id="pdfViewerMetadata" style="font-size: 0.75rem;">Loading file details...</small>
                    </div>
                </div>

                <!-- PDF Navigation & Zoom Toolbar -->
                <div class="d-flex align-items-center gap-2 bg-dark bg-opacity-30 px-3 py-1 rounded-pill">
                    <button class="btn btn-sm btn-outline-light border-0 py-0" id="pdfPrevPage" title="Previous Page"><i class="bi bi-chevron-up"></i></button>
                    <span class="text-white fs-7 d-flex align-items-center gap-1">
                        Page <input type="number" id="pdfPageNum" value="1" min="1" style="width: 48px; text-align: center;" class="form-control form-control-sm d-inline-block py-0 px-1 text-dark fw-bold"> / <span id="pdfPageCount">--</span>
                    </span>
                    <button class="btn btn-sm btn-outline-light border-0 py-0" id="pdfNextPage" title="Next Page"><i class="bi bi-chevron-down"></i></button>

                    <span class="border-end border-secondary mx-1" style="height: 18px;"></span>

                    <button class="btn btn-sm btn-outline-light border-0 py-0" id="pdfZoomOut" title="Zoom Out"><i class="bi bi-zoom-out"></i></button>
                    <span class="text-white fs-7 fw-semibold" id="pdfZoomLevel">100%</span>
                    <button class="btn btn-sm btn-outline-light border-0 py-0" id="pdfZoomIn" title="Zoom In"><i class="bi bi-zoom-in"></i></button>
                </div>

                <!-- Action Controls & Close -->
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="pdfDownloadBtn" class="btn btn-sm btn-light font-weight-bold text-apple-dark d-none" target="_blank">
                        <i class="bi bi-download"></i> Download
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body with Continuous Scroll Container & Watermark Overlay -->
            <div class="modal-body p-0 position-relative bg-dark bg-opacity-75 overflow-auto text-center" id="pdfViewerContainer" style="height: calc(92vh - 56px); scroll-behavior: smooth;">

                <!-- Accreditor Watermark Overlay -->
                @if(auth()->check() && auth()->user()->isAccreditor())
                    <div class="watermark-overlay" style="position: fixed; top: 10%; left: 0; width: 100%; height: 80%; pointer-events: none; z-index: 100;">
                        <div class="watermark-text">
                            FOR ACCREDITATION REVIEW ONLY<br>
                            {{ auth()->user()->name }}<br>
                            {{ date('Y-m-d H:i') }}
                        </div>
                    </div>
                @endif

                <!-- Loading Spinner -->
                <div id="pdfLoadingSpinner" class="my-5 py-5 text-center text-white">
                    <div class="spinner-border text-apple-green" role="status" style="width: 3rem; height: 3rem;"></div>
                    <p class="mt-3 fw-semibold fs-6">Loading PDF Document (Continuous Scroll Mode)...</p>
                </div>

                <!-- Continuous Pages Stack Container -->
                <div id="pdfPagesStack" class="py-4 d-flex flex-column align-items-center gap-3"></div>
            </div>
        </div>
    </div>
</div>

<style>
    .pdf-link-layer {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 10;
    }
    .pdf-annotation-link {
        position: absolute;
        pointer-events: auto;
        background-color: rgba(0, 102, 204, 0.08);
        border: 1px dashed rgba(0, 102, 204, 0.3);
        border-radius: 2px;
        transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
        cursor: pointer;
        text-decoration: none;
    }
    .pdf-annotation-link:hover {
        background-color: rgba(0, 102, 204, 0.28);
        border-style: solid;
        border-color: rgba(0, 102, 204, 0.8);
        box-shadow: 0 2px 6px rgba(0, 102, 204, 0.35);
    }
</style>

<!-- PDF.js Continuous Scroll Renderer -->
<script>
    let pdfDocObj = null;
    let totalPdfPages = 0;
    let pdfScale = 1.15;
    let isRenderingPages = false;
    let pageObserver = null;

    function renderAllPagesContinuous() {
        const stackContainer = document.getElementById('pdfPagesStack');
        stackContainer.innerHTML = '';
        document.getElementById('pdfLoadingSpinner').classList.remove('d-none');

        // Setup IntersectionObserver for auto-syncing page counter on scroll
        if (pageObserver) pageObserver.disconnect();
        pageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const pageNum = entry.target.getAttribute('data-page-num');
                    if (pageNum) {
                        document.getElementById('pdfPageNum').value = pageNum;
                    }
                }
            });
        }, {
            root: document.getElementById('pdfViewerContainer'),
            threshold: 0.5
        });

        let renderPromises = [];

        for (let i = 1; i <= totalPdfPages; i++) {
            const pageWrapper = document.createElement('div');
            pageWrapper.className = 'pdf-page-wrapper shadow-lg bg-white rounded my-2 position-relative';
            pageWrapper.id = `pdf-page-wrapper-${i}`;
            pageWrapper.setAttribute('data-page-num', i);

            const canvas = document.createElement('canvas');
            canvas.id = `pdf-canvas-${i}`;
            canvas.className = 'd-block';

            pageWrapper.appendChild(canvas);
            stackContainer.appendChild(pageWrapper);

            // Observe for page number scroll tracking
            pageObserver.observe(pageWrapper);

            renderPromises.push(renderSinglePage(i, canvas));
        }

        Promise.all(renderPromises).then(() => {
            document.getElementById('pdfLoadingSpinner').classList.add('d-none');
        });
    }

    function renderSinglePage(pageNo, canvas) {
        return pdfDocObj.getPage(pageNo).then(function(page) {
            const viewport = page.getViewport({ scale: pdfScale });
            const ctx = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };

            return page.render(renderContext).promise.then(function() {
                return renderPageLinks(page, pageNo, viewport);
            });
        });
    }

    function renderPageLinks(page, pageNo, viewport) {
        const pageWrapper = document.getElementById(`pdf-page-wrapper-${pageNo}`);
        if (!pageWrapper) return;

        let linkLayer = pageWrapper.querySelector('.pdf-link-layer');
        if (!linkLayer) {
            linkLayer = document.createElement('div');
            linkLayer.className = 'pdf-link-layer';
            pageWrapper.appendChild(linkLayer);
        } else {
            linkLayer.innerHTML = '';
        }

        const existingRects = [];

        // 1. Render explicit PDF Link Annotations
        return page.getAnnotations().then(function(annotations) {
            annotations.forEach(function(annotation) {
                if (annotation.subtype === 'Link') {
                    const rect = viewport.convertToViewportRectangle(annotation.rect);
                    const left = Math.min(rect[0], rect[2]);
                    const top = Math.min(rect[1], rect[3]);
                    const width = Math.abs(rect[2] - rect[0]);
                    const height = Math.abs(rect[3] - rect[1]);

                    existingRects.push({ left, top, width, height });

                    const targetUrl = annotation.url || annotation.unsafeUrl;
                    if (targetUrl) {
                        createOverlayLink(linkLayer, targetUrl, left, top, width, height);
                    } else if (annotation.dest) {
                        createInternalJumpLink(linkLayer, annotation.dest, left, top, width, height);
                    }
                }
            });

            // 2. Render unannotated plain-text URLs in PDF text layer
            return page.getTextContent().then(function(textContent) {
                const urlRegex = /(https?:\/\/[^\s]+|www\.[^\s]+)/gi;
                textContent.items.forEach(function(item) {
                    const text = item.str;
                    if (!text) return;
                    let match;
                    while ((match = urlRegex.exec(text)) !== null) {
                        let matchedUrl = match[0];
                        // Clean up trailing punctuation if any
                        matchedUrl = matchedUrl.replace(/[.,;)]+$/, '');
                        const fullUrl = matchedUrl.startsWith('www.') ? 'https://' + matchedUrl : matchedUrl;

                        const tx = item.transform;
                        const fontHeight = Math.sqrt(tx[2] * tx[2] + tx[3] * tx[3]) || Math.abs(tx[3]) || 12;
                        const pdfX = tx[4];
                        const pdfY = tx[5];
                        const pdfW = item.width || (text.length * fontHeight * 0.5);
                        const pdfH = fontHeight;

                        const rect = viewport.convertToViewportRectangle([pdfX, pdfY, pdfX + pdfW, pdfY + pdfH]);
                        const left = Math.min(rect[0], rect[2]);
                        const top = Math.min(rect[1], rect[3]);
                        const width = Math.abs(rect[2] - rect[0]);
                        const height = Math.abs(rect[3] - rect[1]);

                        // Skip if already covered by an annotation link
                        const isDuplicate = existingRects.some(r =>
                            Math.abs(r.left - left) < 20 && Math.abs(r.top - top) < 20
                        );

                        if (!isDuplicate && width > 0 && height > 0) {
                            createOverlayLink(linkLayer, fullUrl, left, top, width, height);
                        }
                    }
                });
            });
        }).catch(function(err) {
            console.warn('Error rendering links for PDF page ' + pageNo, err);
        });
    }

    function createOverlayLink(container, url, left, top, width, height) {
        const a = document.createElement('a');
        a.href = url;
        a.target = '_blank';
        a.rel = 'noopener noreferrer';
        a.className = 'pdf-annotation-link';
        a.title = 'Open link in new tab: ' + url;
        a.style.left = left + 'px';
        a.style.top = top + 'px';
        a.style.width = width + 'px';
        a.style.height = height + 'px';

        a.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            window.open(url, '_blank', 'noopener,noreferrer');
        });

        container.appendChild(a);
    }

    function createInternalJumpLink(container, dest, left, top, width, height) {
        const a = document.createElement('a');
        a.href = '#';
        a.className = 'pdf-annotation-link';
        a.title = 'Jump to section inside document';
        a.style.left = left + 'px';
        a.style.top = top + 'px';
        a.style.width = width + 'px';
        a.style.height = height + 'px';

        a.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (typeof dest === 'string') {
                pdfDocObj.getDestination(dest).then(function(explicitDest) {
                    if (explicitDest && explicitDest[0]) {
                        pdfDocObj.getPageIndex(explicitDest[0]).then(function(pageIndex) {
                            scrollToPdfPage(pageIndex + 1);
                        });
                    }
                });
            } else if (Array.isArray(dest) && dest[0]) {
                pdfDocObj.getPageIndex(dest[0]).then(function(pageIndex) {
                    scrollToPdfPage(pageIndex + 1);
                });
            }
        });

        container.appendChild(a);
    }

    document.getElementById('pdfPrevPage').addEventListener('click', function() {
        let current = parseInt(document.getElementById('pdfPageNum').value) || 1;
        if (current > 1) {
            scrollToPdfPage(current - 1);
        }
    });

    document.getElementById('pdfNextPage').addEventListener('click', function() {
        let current = parseInt(document.getElementById('pdfPageNum').value) || 1;
        if (current < totalPdfPages) {
            scrollToPdfPage(current + 1);
        }
    });

    document.getElementById('pdfPageNum').addEventListener('change', function(e) {
        let desired = parseInt(e.target.value);
        if (desired >= 1 && desired <= totalPdfPages) {
            scrollToPdfPage(desired);
        }
    });

    function scrollToPdfPage(pageNo) {
        const targetWrapper = document.getElementById(`pdf-page-wrapper-${pageNo}`);
        if (targetWrapper) {
            targetWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
            document.getElementById('pdfPageNum').value = pageNo;
        }
    }

    document.getElementById('pdfZoomIn').addEventListener('click', function() {
        pdfScale += 0.2;
        document.getElementById('pdfZoomLevel').innerText = Math.round(pdfScale * 100) + '%';
        if (pdfDocObj) renderAllPagesContinuous();
    });

    document.getElementById('pdfZoomOut').addEventListener('click', function() {
        if (pdfScale <= 0.5) return;
        pdfScale -= 0.2;
        document.getElementById('pdfZoomLevel').innerText = Math.round(pdfScale * 100) + '%';
        if (pdfDocObj) renderAllPagesContinuous();
    });

    function openPdfModal(streamUrl, downloadUrl, title, metadata, canDownload) {
        pdfScale = 1.15;
        document.getElementById('pdfZoomLevel').innerText = '115%';
        document.getElementById('pdfViewerTitle').innerText = title;
        document.getElementById('pdfViewerMetadata').innerText = metadata;

        const downloadBtn = document.getElementById('pdfDownloadBtn');
        if (canDownload && downloadUrl) {
            downloadBtn.href = downloadUrl;
            downloadBtn.classList.remove('d-none');
        } else {
            downloadBtn.classList.add('d-none');
        }

        const modalEl = document.getElementById('pdfViewerModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        document.getElementById('pdfLoadingSpinner').classList.remove('d-none');
        document.getElementById('pdfPagesStack').innerHTML = '';

        pdfjsLib.getDocument(streamUrl).promise.then(function(pdfDoc_) {
            pdfDocObj = pdfDoc_;
            totalPdfPages = pdfDocObj.numPages;
            document.getElementById('pdfPageCount').innerText = totalPdfPages;
            document.getElementById('pdfPageNum').value = 1;
            renderAllPagesContinuous();
        }).catch(function(error) {
            document.getElementById('pdfLoadingSpinner').innerHTML = '<div class="alert alert-danger my-5 mx-auto" style="max-width: 500px;">Error loading PDF document. Access forbidden or file missing.</div>';
        });
    }
</script>
