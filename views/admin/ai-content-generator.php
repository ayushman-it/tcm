<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Content Generator | TCM Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .content-status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .lesson-card {
            transition: all 0.2s;
            cursor: pointer;
        }
        .lesson-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .generating {
            opacity: 0.6;
            pointer-events: none;
        }
        .code-preview {
            max-height: 200px;
            overflow-y: auto;
            background: #f8f9fa;
            border-radius: 4px;
            padding: 1rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-robot"></i> AI Content Generator</h2>
                <p class="text-muted">Automatically generate detailed lesson content with examples and exercises</p>
            </div>
            <div>
                <button class="btn btn-primary" id="refreshBtn">
                    <i class="bi bi-arrow-clockwise"></i> Refresh Status
                </button>
            </div>
        </div>

        <!-- API Key Check -->
        <div class="alert alert-warning" id="apiKeyWarning" style="display: none;">
            <i class="bi bi-exclamation-triangle"></i> 
            <strong>OpenAI API Key not configured!</strong> 
            Please set <code>OPENAI_API_KEY</code> in your <code>.env</code> file.
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Course</label>
                        <select class="form-select" id="courseFilter">
                            <option value="">All Courses</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="missing">Missing Content</option>
                            <option value="draft">Draft</option>
                            <option value="reviewed">Reviewed</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Language</label>
                        <select class="form-select" id="languageSelect">
                            <option value="hi+en">Hindi + English</option>
                            <option value="hi">Hindi Only</option>
                            <option value="en">English Only</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-success w-100" id="generateAllBtn">
                            <i class="bi bi-magic"></i> Generate All Missing
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content List -->
        <div id="contentList">
            <div class="text-center py-5">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Preview Modal -->
    <div class="modal fade" id="contentModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contentModalTitle">Lesson Content</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="contentModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border" role="status"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" id="regenerateBtn">
                        <i class="bi bi-arrow-clockwise"></i> Regenerate
                    </button>
                    <button type="button" class="btn btn-success" id="publishBtn">
                        <i class="bi bi-check-circle"></i> Publish
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentLessonId = null;
        let contentData = {};

        // Load courses for filter
        async function loadCourses() {
            try {
                const res = await fetch('/admin/courses/list');
                const data = await res.json();
                const select = document.getElementById('courseFilter');
                
                data.courses.forEach(course => {
                    const option = document.createElement('option');
                    option.value = course.id;
                    option.textContent = course.title;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Failed to load courses:', error);
            }
        }

        // Load content status
        async function loadContentStatus() {
            try {
                const courseId = document.getElementById('courseFilter').value;
                const url = courseId ? `/admin/ai-content/status?course_id=${courseId}` : '/admin/ai-content/status';
                
                const res = await fetch(url);
                const data = await res.json();
                
                if (!data.success) {
                    throw new Error(data.message);
                }

                renderContentList(data.lessons);
            } catch (error) {
                console.error('Failed to load content status:', error);
                if (error.message.includes('API key')) {
                    document.getElementById('apiKeyWarning').style.display = 'block';
                }
                document.getElementById('contentList').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> ${error.message}
                    </div>
                `;
            }
        }

        // Render content list
        function renderContentList(lessons) {
            const statusFilter = document.getElementById('statusFilter').value;
            let html = '';

            for (const [courseName, modules] of Object.entries(lessons)) {
                html += `<div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-book"></i> ${courseName}</h5>
                    </div>
                    <div class="card-body">`;

                for (const [moduleName, lessonsList] of Object.entries(modules)) {
                    html += `<h6 class="text-secondary mt-3"><i class="bi bi-folder2"></i> ${moduleName}</h6>
                        <div class="row g-2">`;

                    lessonsList.forEach(lesson => {
                        // Apply status filter
                        const lessonStatus = lesson.has_content ? lesson.status : 'missing';
                        if (statusFilter && lessonStatus !== statusFilter) return;

                        const statusBadge = getStatusBadge(lesson);
                        const actionBtn = getActionButton(lesson);

                        html += `
                            <div class="col-md-6">
                                <div class="lesson-card card h-100" data-lesson-id="${lesson.lesson_id}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0">${lesson.title}</h6>
                                            ${statusBadge}
                                        </div>
                                        <div class="d-flex gap-2 mt-2">
                                            <small class="text-muted">
                                                <i class="bi bi-tag"></i> ${lesson.type}
                                            </small>
                                            ${lesson.language ? `<small class="text-muted"><i class="bi bi-translate"></i> ${lesson.language}</small>` : ''}
                                        </div>
                                        <div class="mt-3">
                                            ${actionBtn}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    html += `</div>`;
                }

                html += `</div></div>`;
            }

            document.getElementById('contentList').innerHTML = html || '<div class="alert alert-info">No lessons found</div>';

            // Add event listeners
            attachEventListeners();
        }

        // Get status badge HTML
        function getStatusBadge(lesson) {
            if (!lesson.has_content) {
                return '<span class="badge bg-secondary content-status-badge">No Content</span>';
            }
            
            const badges = {
                'draft': '<span class="badge bg-warning content-status-badge">Draft</span>',
                'reviewed': '<span class="badge bg-info content-status-badge">Reviewed</span>',
                'published': '<span class="badge bg-success content-status-badge">Published</span>'
            };
            
            return badges[lesson.status] || '';
        }

        // Get action button HTML
        function getActionButton(lesson) {
            if (!lesson.has_content) {
                return `<button class="btn btn-sm btn-primary generate-btn" data-lesson-id="${lesson.lesson_id}">
                    <i class="bi bi-magic"></i> Generate Content
                </button>`;
            }
            
            return `<button class="btn btn-sm btn-outline-primary view-btn" data-lesson-id="${lesson.lesson_id}">
                <i class="bi bi-eye"></i> View Content
            </button>`;
        }

        // Attach event listeners
        function attachEventListeners() {
            // Generate buttons
            document.querySelectorAll('.generate-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const lessonId = btn.dataset.lessonId;
                    generateContent(lessonId);
                });
            });

            // View buttons
            document.querySelectorAll('.view-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const lessonId = btn.dataset.lessonId;
                    viewContent(lessonId);
                });
            });
        }

        // Generate content for lesson
        async function generateContent(lessonId) {
            const language = document.getElementById('languageSelect').value;
            const btn = document.querySelector(`.generate-btn[data-lesson-id="${lessonId}"]`);
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generating...';

            try {
                const res = await fetch('/admin/ai-content/generate-lesson', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ lesson_id: lessonId, language })
                });

                const data = await res.json();

                if (!data.success) {
                    throw new Error(data.message);
                }

                alert('✅ Content generated successfully!');
                loadContentStatus();
            } catch (error) {
                alert('❌ Failed to generate content: ' + error.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-magic"></i> Generate Content';
            }
        }

        // View content
        async function viewContent(lessonId) {
            currentLessonId = lessonId;
            const modal = new bootstrap.Modal(document.getElementById('contentModal'));
            modal.show();

            try {
                const res = await fetch(`/admin/ai-content/lesson/${lessonId}`);
                const data = await res.json();

                if (!data.success) {
                    throw new Error(data.message);
                }

                contentData = data;
                renderContentPreview(data);
            } catch (error) {
                document.getElementById('contentModalBody').innerHTML = `
                    <div class="alert alert-danger">${error.message}</div>
                `;
            }
        }

        // Render content preview
        function renderContentPreview(data) {
            const html = `
                <h5>Overview</h5>
                <p>${data.overview_en || ''}</p>
                ${data.overview_hi ? `<p class="text-muted">${data.overview_hi}</p>` : ''}

                <h5 class="mt-4">Key Concepts (${data.key_concepts?.length || 0})</h5>
                <ul class="list-group mb-3">
                    ${(data.key_concepts || []).map(c => `
                        <li class="list-group-item">
                            <strong>${c.title_en}</strong>
                            <p class="mb-0 text-muted small">${c.explanation_en?.substring(0, 150)}...</p>
                        </li>
                    `).join('')}
                </ul>

                <h5>Code Examples (${data.code_examples?.length || 0})</h5>
                ${(data.code_examples || []).map((ex, i) => `
                    <div class="card mb-2">
                        <div class="card-header">${ex.title}</div>
                        <div class="card-body">
                            <pre class="code-preview"><code>${escapeHtml(ex.code || '')}</code></pre>
                        </div>
                    </div>
                `).join('')}

                <h5>Exercises (${data.exercises?.length || 0})</h5>
                <ul class="list-group">
                    ${(data.exercises || []).map(ex => `
                        <li class="list-group-item">
                            <strong>${ex.title}</strong>
                            <span class="badge bg-${ex.difficulty === 'beginner' ? 'success' : ex.difficulty === 'intermediate' ? 'warning' : 'danger'} ms-2">
                                ${ex.difficulty}
                            </span>
                            <p class="mb-0 text-muted small mt-1">${ex.description_en?.substring(0, 100)}...</p>
                        </li>
                    `).join('')}
                </ul>
            `;

            document.getElementById('contentModalBody').innerHTML = html;
            document.getElementById('contentModalTitle').textContent = `Lesson ${currentLessonId} Content`;
        }

        // Publish content
        document.getElementById('publishBtn').addEventListener('click', async () => {
            if (!currentLessonId) return;

            try {
                const res = await fetch('/admin/ai-content/publish', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ lesson_id: currentLessonId })
                });

                const data = await res.json();

                if (!data.success) {
                    throw new Error(data.message);
                }

                alert('✅ Content published successfully!');
                bootstrap.Modal.getInstance(document.getElementById('contentModal')).hide();
                loadContentStatus();
            } catch (error) {
                alert('❌ Failed to publish: ' + error.message);
            }
        });

        // Regenerate content
        document.getElementById('regenerateBtn').addEventListener('click', async () => {
            if (!currentLessonId) return;
            if (!confirm('Are you sure you want to regenerate this content?')) return;

            const language = document.getElementById('languageSelect').value;

            try {
                const res = await fetch('/admin/ai-content/regenerate-lesson', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ lesson_id: currentLessonId, language })
                });

                const data = await res.json();

                if (!data.success) {
                    throw new Error(data.message);
                }

                alert('✅ Content regenerated successfully!');
                viewContent(currentLessonId); // Reload content
            } catch (error) {
                alert('❌ Failed to regenerate: ' + error.message);
            }
        });

        // Filters
        document.getElementById('courseFilter').addEventListener('change', loadContentStatus);
        document.getElementById('statusFilter').addEventListener('change', () => {
            renderContentList(contentData.lessons);
        });
        document.getElementById('refreshBtn').addEventListener('click', loadContentStatus);

        // Utility
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Initialize
        loadCourses();
        loadContentStatus();
    </script>
</body>
</html>
