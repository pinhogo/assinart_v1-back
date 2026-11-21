<?php

namespace App\Console\Commands;

use App\Services\ValidationKpiReportService;
use Illuminate\Console\Command;

class ValidationKpiReportCommand extends Command
{
    protected $signature = 'metrics:validation-report
        {--days=30 : Janela de dias para o relatório}
        {--json : Exibir saída em JSON}';

    protected $description = 'Gera relatório consolidado dos KPIs de validação e respostas de feature_leads.';

    public function handle(ValidationKpiReportService $service): int
    {
        $days = max(1, (int) $this->option('days'));
        $report = $service->generate($days);

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }

        $this->info("Relatório de Validação — Últimos {$report['period']['days']} dias");
        $this->renderKpisTable($report['kpis']);
        $this->renderKrsTable($report['krs']);
        $this->renderLeadsSummary($report['leads_breakdown']);

        return self::SUCCESS;
    }

    /**
     * @param  array<string, array<string, mixed>>  $kpis
     */
    private function renderKpisTable(array $kpis): void
    {
        $rows = [
            [
                'KPI 1: artist_activation_rate',
                $kpis['artist_activation_rate']['value'] . '%',
                $kpis['artist_activation_rate']['target'],
                "{$kpis['artist_activation_rate']['activated_artists']}/{$kpis['artist_activation_rate']['total_artists']} artistas",
            ],
            [
                'KPI 2: public_links_ctr',
                $kpis['public_links_ctr']['value'] . '%',
                $kpis['public_links_ctr']['target'],
                "{$kpis['public_links_ctr']['clicks']} cliques / {$kpis['public_links_ctr']['page_views']} views",
            ],
            [
                'KPI 3: editor_vitrine_intent_rate',
                $kpis['editor_vitrine_intent_rate']['value'] . '%',
                $kpis['editor_vitrine_intent_rate']['target'],
                "{$kpis['editor_vitrine_intent_rate']['vitrine_cta_clicks']}/{$kpis['editor_vitrine_intent_rate']['editor_sessions']} sessões",
            ],
            [
                'KPI 4: landing_conversion_rate',
                $kpis['landing_conversion_rate']['value'] . '%',
                $kpis['landing_conversion_rate']['target'],
                "{$kpis['landing_conversion_rate']['new_artists']} cadastros + {$kpis['landing_conversion_rate']['home_leads']} leads / {$kpis['landing_conversion_rate']['home_unique_visitors']} visitantes",
            ],
        ];

        $this->table(['KPI', 'Valor', 'Meta', 'Detalhes'], $rows);
    }

    /**
     * @param  array<string, array<string, mixed>>  $krs
     */
    private function renderKrsTable(array $krs): void
    {
        $rows = [
            [
                'KR1: Artistas ativos (7d, >=20 views)',
                $krs['kr1_active_artists_weekly']['current'],
                $krs['kr1_active_artists_weekly']['target'],
                $krs['kr1_active_artists_weekly']['progress_pct'] . '%',
            ],
            [
                'KR2: Leads Vitrine (total)',
                $krs['kr2_vitrine_leads_total']['current'],
                $krs['kr2_vitrine_leads_total']['target'],
                $krs['kr2_vitrine_leads_total']['progress_pct'] . '%',
            ],
        ];

        $this->table(['KR', 'Atual', 'Meta', 'Progresso'], $rows);
    }

    /**
     * @param  array<string, mixed>  $leads
     */
    private function renderLeadsSummary(array $leads): void
    {
        $this->info("Feature Leads (Total: {$leads['total']})");

        $rows = [];
        foreach ($leads['by_feature'] as $feature => $count) {
            $rows[] = ['feature', $feature, $count];
        }
        foreach ($leads['by_source'] as $source => $count) {
            $rows[] = ['source', $source, $count];
        }
        foreach ($leads['selling_preferences'] as $pref => $count) {
            $rows[] = ['selling_preference', $pref, $count];
        }

        $this->table(['Categoria', 'Chave', 'Quantidade'], $rows);
    }
}
