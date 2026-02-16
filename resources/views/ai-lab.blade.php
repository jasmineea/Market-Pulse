<x-guest-layout>
    <x-slot:main>
        {{-- Hero --}}
        <section class="py-16 md:py-24 px-6">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight mb-4">
                    AI Lab
                </h1>
                <h2 class="text-xl md:text-2xl font-semibold text-[#16a34a] mb-6">
                    Ethical AI Infrastructure for Regulated Markets
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto mb-6">
                    TerpInsights AI Lab is a research-grade applied AI testbed designed to evaluate transparency, bias, and robustness in forecasting systems deployed within regulated public markets.
                </p>
                <p class="text-gray-500 text-sm italic max-w-xl mx-auto mb-10">
                    Built in Maryland. Designed for broader institutional collaboration.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#core-modules" class="inline-flex justify-center items-center px-6 py-3.5 rounded-md bg-[#16a34a] text-white font-medium hover:bg-[#15803d] transition-colors">
                        Explore the Framework
                    </a>
                    <a href="{{ route('ai-lab.collaborate') }}" class="inline-flex justify-center items-center px-6 py-3.5 rounded-md border-2 border-gray-300 text-gray-800 font-medium hover:border-gray-400 hover:bg-gray-50 transition-colors">
                        Request Collaboration Discussion
                    </a>
                </div>
            </div>
        </section>

        {{-- Why This Exists --}}
        <section class="py-16 md:py-24 px-6 bg-gray-50">
            <div class="max-w-3xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 text-center mb-6">Why an AI Lab?</h2>
                <p class="text-gray-600 mb-6">
                    AI systems increasingly influence public markets, regulatory decisions, and economic opportunity. Yet most forecasting tools operate as black boxes — with limited documentation, minimal bias testing, and no structured stress evaluation.
                </p>
                <p class="text-gray-900 font-semibold mb-6">The AI Lab exists to change that.</p>
                <p class="text-gray-600 mb-8">
                    This initiative transforms Maryland's regulated cannabis market into a live validation environment for:
                </p>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-start gap-2"><span class="text-[#16a34a] mt-1">•</span><span class="text-gray-700">Interpretable forecasting</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#16a34a] mt-1">•</span><span class="text-gray-700">Segment-level bias detection</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#16a34a] mt-1">•</span><span class="text-gray-700">Policy shock simulation</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#16a34a] mt-1">•</span><span class="text-gray-700">Equity participation measurement</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#16a34a] mt-1">•</span><span class="text-gray-700">Transparent model documentation</span></li>
                </ul>
                <p class="text-gray-700 font-medium italic">
                    The goal is not just prediction accuracy — but accountable deployment.
                </p>
            </div>
        </section>

        {{-- Core Modules --}}
        <section id="core-modules" class="py-16 md:py-24 px-6 scroll-mt-24">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 text-center mb-4">Core Modules (Preview)</h2>
                <p class="text-gray-600 text-center mb-12 max-w-2xl mx-auto">Structured frameworks for interpretability, fairness, and robustness.</p>

                <div class="space-y-10">
                    {{-- 1. Forecast Lab --}}
                    <div class="bg-white rounded-lg border border-gray-200 p-6 md:p-8 shadow-sm">
                        <div class="flex items-start gap-4">
                            <span class="flex-shrink-0 w-10 h-10 rounded-lg bg-[#16a34a]/10 flex items-center justify-center text-[#16a34a] font-bold text-lg">1</span>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">Forecast Lab</h3>
                                <p class="text-gray-600 mb-4">Interpretable revenue and volume forecasting with documented accuracy metrics, confidence intervals, and feature explainability.</p>
                                <p class="text-sm font-medium text-gray-700 mb-2">Includes:</p>
                                <ul class="text-gray-600 text-sm space-y-1">
                                    <li>• MAE, MAPE, RMSE, R² reporting</li>
                                    <li>• Time-series cross validation</li>
                                    <li>• Feature importance diagnostics</li>
                                    <li>• Automated retraining triggers</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Fairness Audit --}}
                    <div class="bg-white rounded-lg border border-gray-200 p-6 md:p-8 shadow-sm">
                        <div class="flex items-start gap-4">
                            <span class="flex-shrink-0 w-10 h-10 rounded-lg bg-[#16a34a]/10 flex items-center justify-center text-[#16a34a] font-bold text-lg">2</span>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">Fairness Audit</h3>
                                <p class="text-gray-600 mb-4">Segment-level error analysis and parity diagnostics to detect underprediction or overprediction across equity-sensitive populations.</p>
                                <p class="text-sm font-medium text-gray-700 mb-2">Includes:</p>
                                <ul class="text-gray-600 text-sm space-y-1">
                                    <li>• Error gap tracking</li>
                                    <li>• Parity threshold flags</li>
                                    <li>• Sample size confidence warnings</li>
                                    <li>• Remediation protocol documentation</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Stress Testing --}}
                    <div class="bg-white rounded-lg border border-gray-200 p-6 md:p-8 shadow-sm">
                        <div class="flex items-start gap-4">
                            <span class="flex-shrink-0 w-10 h-10 rounded-lg bg-[#16a34a]/10 flex items-center justify-center text-[#16a34a] font-bold text-lg">3</span>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">Stress Testing</h3>
                                <p class="text-gray-600 mb-4">Simulated regulatory and market shocks to assess model robustness under policy changes.</p>
                                <p class="text-sm font-medium text-gray-700 mb-2">Includes:</p>
                                <ul class="text-gray-600 text-sm space-y-1">
                                    <li>• Tax change scenarios</li>
                                    <li>• Policy event mapping</li>
                                    <li>• Revenue impact modeling</li>
                                    <li>• Robustness scoring</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Equity & Access Metrics --}}
                    <div class="bg-white rounded-lg border border-gray-200 p-6 md:p-8 shadow-sm">
                        <div class="flex items-start gap-4">
                            <span class="flex-shrink-0 w-10 h-10 rounded-lg bg-[#16a34a]/10 flex items-center justify-center text-[#16a34a] font-bold text-lg">4</span>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">Equity & Access Metrics</h3>
                                <p class="text-gray-600 mb-4">Quantitative indicators measuring market participation, opportunity distribution, and disparity trends.</p>
                                <p class="text-sm font-medium text-gray-700 mb-2">Includes:</p>
                                <ul class="text-gray-600 text-sm space-y-1">
                                    <li>• Revenue share by equity status</li>
                                    <li>• Regional participation tracking</li>
                                    <li>• Experimental Opportunity Index (under review)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Data & Method Transparency --}}
                    <div class="bg-white rounded-lg border border-gray-200 p-6 md:p-8 shadow-sm">
                        <div class="flex items-start gap-4">
                            <span class="flex-shrink-0 w-10 h-10 rounded-lg bg-[#16a34a]/10 flex items-center justify-center text-[#16a34a] font-bold text-lg">5</span>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">Data & Method Transparency</h3>
                                <p class="text-gray-600 mb-4">Full documentation of:</p>
                                <ul class="text-gray-600 text-sm space-y-1 mb-4">
                                    <li>• Data sources</li>
                                    <li>• Feature engineering</li>
                                    <li>• Model architecture</li>
                                    <li>• Evaluation methodology</li>
                                    <li>• Known limitations</li>
                                </ul>
                                <p class="text-gray-900 font-semibold">No black boxes.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Research Status --}}
        <section class="py-16 md:py-24 px-6 bg-gray-50">
            <div class="max-w-3xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 text-center mb-6">Research Status</h2>
                <div class="bg-white rounded-lg border border-gray-200 p-6 md:p-8 shadow-sm">
                    <p class="text-lg font-semibold text-[#16a34a] mb-4">Current Status: Active Development (v1)</p>
                    <p class="text-gray-600 mb-6">The AI Lab is currently operating as a structured prototype.</p>
                    <ul class="space-y-2 text-gray-700 mb-6">
                        <li class="flex items-center gap-2"><span class="text-[#16a34a]">✓</span> Forecast models deployed and monitored monthly</li>
                        <li class="flex items-center gap-2"><span class="text-[#16a34a]">✓</span> Fairness testing framework v1 implemented</li>
                        <li class="flex items-center gap-2"><span class="text-[#16a34a]">✓</span> Policy NLP module under development</li>
                        <li class="flex items-center gap-2"><span class="text-[#16a34a]">✓</span> Methodology documentation open for institutional review</li>
                    </ul>
                    <p class="text-gray-600">
                        We are actively exploring academic collaboration to refine fairness metrics, expand intersectional analysis, and formalize validation standards.
                    </p>
                </div>
            </div>
        </section>

        {{-- Who This Is For --}}
        <section class="py-16 md:py-24 px-6">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">Who This Is For</h2>
                <p class="text-gray-600 mb-8">The AI Lab is designed for:</p>
                <div class="flex flex-wrap justify-center gap-3">
                    @foreach(['AI ethics researchers', 'Public policy scholars', 'Regulatory agencies', 'Economic development organizations', 'Civic data practitioners'] as $audience)
                        <span class="inline-block px-4 py-2 rounded-full bg-gray-100 text-gray-700 text-sm font-medium">{{ $audience }}</span>
                    @endforeach
                </div>
                <p class="text-gray-600 mt-8 max-w-2xl mx-auto">
                    If you are studying responsible AI deployment in real-world systems, this environment is intended to serve as a collaborative validation space.
                </p>
            </div>
        </section>

        {{-- Institutional Collaboration --}}
        <section class="py-16 md:py-24 px-6 bg-gray-50">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">Partnership & Research Inquiry</h2>
                <p class="text-gray-600 mb-8">
                    TerpInsights is exploring collaboration with universities and research centers focused on:
                </p>
                <ul class="text-gray-700 text-left max-w-md mx-auto space-y-2 mb-8">
                    <li class="flex items-center gap-2"><span class="text-[#16a34a]">•</span> Bias detection & remediation</li>
                    <li class="flex items-center gap-2"><span class="text-[#16a34a]">•</span> Model stress testing frameworks</li>
                    <li class="flex items-center gap-2"><span class="text-[#16a34a]">•</span> AI governance standards</li>
                    <li class="flex items-center gap-2"><span class="text-[#16a34a]">•</span> Equity measurement methodologies</li>
                </ul>
                <p class="text-gray-600 mb-8">If you are interested in research partnership or structured evaluation, we welcome conversation.</p>
                <a href="{{ route('ai-lab.collaborate') }}" class="inline-flex justify-center items-center px-6 py-3.5 rounded-md bg-[#16a34a] text-white font-medium hover:bg-[#15803d] transition-colors">
                    Request Collaboration Discussion
                </a>
            </div>
        </section>

        {{-- Footer line --}}
        <section class="py-12 px-6 border-t border-gray-200">
            <p class="text-sm text-gray-500 text-center max-w-2xl mx-auto italic">
                TerpInsights AI Lab is an applied civic AI infrastructure initiative focused on transparency, fairness, and accountable deployment in regulated markets.
            </p>
        </section>

        {{-- Waitlist modal --}}
        <x-waitlist-modal source-page="ai-lab" />
    </x-slot:main>
</x-guest-layout>
