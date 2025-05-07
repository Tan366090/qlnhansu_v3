// AI analysis module
class AIAnalysis {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadAnalysisData();
    }

    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            const analyzeButton = document.querySelector('.analyze-data');
            if (analyzeButton) {
                analyzeButton.addEventListener('click', () => {
                    this.analyzeData();
                });
            }

            const exportButton = document.querySelector('.export-analysis');
            if (exportButton) {
                exportButton.addEventListener('click', () => {
                    this.exportAnalysis();
                });
            }
        });
    }

    loadAnalysisData() {
        fetch('/api/ai-analysis')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.updateAnalysisUI(data.data);
                }
            })
            .catch(error => {
                console.error('Error loading AI analysis:', error);
            });
    }

    updateAnalysisUI(analysis) {
        // Update trends
        this.updateTrends(analysis.trends);

        // Update predictions
        this.updatePredictions(analysis.predictions);

        // Update recommendations
        this.updateRecommendations(analysis.recommendations);

        // Update insights
        this.updateInsights(analysis.insights);
    }

    updateTrends(trends) {
        const container = document.querySelector('.trends-container');
        if (container) {
            container.innerHTML = trends
                .map(trend => `
                    <div class="trend-item">
                        <div class="trend-title">${trend.title}</div>
                        <div class="trend-value ${trend.change >= 0 ? 'positive' : 'negative'}">
                            ${trend.value} (${trend.change >= 0 ? '+' : ''}${trend.change}%)
                        </div>
                        <div class="trend-chart">
                            <canvas class="trend-chart-canvas" data-values="${trend.values.join(',')}"></canvas>
                        </div>
                    </div>
                `)
                .join('');

            // Initialize trend charts
            const charts = container.querySelectorAll('.trend-chart-canvas');
            charts.forEach(chart => {
                const values = chart.dataset.values.split(',').map(Number);
                new Chart(chart, {
                    type: 'line',
                    data: {
                        labels: Array.from({length: values.length}, (_, i) => i + 1),
                        datasets: [{
                            data: values,
                            borderColor: 'rgb(75, 192, 192)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            });
        }
    }

    updatePredictions(predictions) {
        const container = document.querySelector('.predictions-container');
        if (container) {
            container.innerHTML = predictions
                .map(prediction => `
                    <div class="prediction-item">
                        <div class="prediction-title">${prediction.title}</div>
                        <div class="prediction-value">${prediction.value}</div>
                        <div class="prediction-confidence">
                            Confidence: ${prediction.confidence}%
                        </div>
                    </div>
                `)
                .join('');
        }
    }

    updateRecommendations(recommendations) {
        const container = document.querySelector('.recommendations-container');
        if (container) {
            container.innerHTML = recommendations
                .map(recommendation => `
                    <div class="recommendation-item">
                        <div class="recommendation-icon">
                            <i class="fas ${this.getRecommendationIcon(recommendation.type)}"></i>
                        </div>
                        <div class="recommendation-content">
                            <div class="recommendation-title">${recommendation.title}</div>
                            <div class="recommendation-description">${recommendation.description}</div>
                        </div>
                    </div>
                `)
                .join('');
        }
    }

    updateInsights(insights) {
        const container = document.querySelector('.insights-container');
        if (container) {
            container.innerHTML = insights
                .map(insight => `
                    <div class="insight-item">
                        <div class="insight-title">${insight.title}</div>
                        <div class="insight-content">${insight.content}</div>
                        <div class="insight-impact">
                            Impact: ${insight.impact}
                        </div>
                    </div>
                `)
                .join('');
        }
    }

    getRecommendationIcon(type) {
        const icons = {
            'optimization': 'fa-magic',
            'alert': 'fa-exclamation-triangle',
            'suggestion': 'fa-lightbulb',
            'improvement': 'fa-chart-line'
        };
        return icons[type] || 'fa-info-circle';
    }

    analyzeData() {
        const loadingOverlay = document.querySelector('.loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.style.display = 'flex';
        }

        fetch('/api/analyze-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateAnalysisUI(data.data);
            }
        })
        .catch(error => {
            console.error('Error analyzing data:', error);
        })
        .finally(() => {
            if (loadingOverlay) {
                loadingOverlay.style.display = 'none';
            }
        });
    }

    exportAnalysis() {
        fetch('/api/export-analysis')
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'ai_analysis_report.pdf';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            })
            .catch(error => {
                console.error('Error exporting analysis:', error);
            });
    }
}

// Initialize AI analysis functionality
const aiAnalysis = new AIAnalysis(); 