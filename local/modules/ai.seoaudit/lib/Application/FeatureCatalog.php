<?php

namespace Ai\SeoAudit\Application;

use Ai\SeoAudit\Domain\PackagePlan;

final class FeatureCatalog
{
    /**
     * @return PackagePlan[]
     */
    public static function plans(): array
    {
        return [
            new PackagePlan('start', 'Start', [
                'position_monitoring',
                'semantic_core_collection',
                'query_clustering',
                'serp_analysis',
                'technical_audit',
                'snippet_ai_recommendations',
                'title_description_h1_generation',
            ], [
                'projects' => 3,
                'keywords_per_project' => 5000,
            ]),
            new PackagePlan('pro', 'Pro', [
                'competitor_auto_discovery',
                'topic_map',
                'vector_semantic_embeddings',
                'structure_compare',
                'backlink_competitor_analysis',
                'benchmark_score',
                'serp_heatmap',
                'ai_internal_linking',
                'seo_action_planner',
            ], [
                'projects' => 15,
                'keywords_per_project' => 50000,
            ]),
            new PackagePlan('enterprise', 'Enterprise', [
                'gsc_serp_semantic_core',
                'ml_rank_prediction',
                'ai_traffic_forecast',
                'serp_volatility_tracking',
                'multilanguage_pipeline',
                'rag_site_knowledge',
                'task_scheduler',
                'audit_comparison_by_time',
            ], [
                'projects' => 100,
                'keywords_per_project' => 500000,
            ]),
            new PackagePlan('autonomous', 'Autonomous', [
                'auto_topical_authority_expansion',
                'knowledge_graph',
                'ai_seo_strategy_12m',
                'robots_sitemap_automanagement',
                'user_behavior_analysis',
                'full_ai_growth_engine',
                'multi_agent_ai_system',
                'reinforcement_learning_optimization',
                'full_autonomous_website',
            ], [
                'projects' => PHP_INT_MAX,
                'keywords_per_project' => PHP_INT_MAX,
            ]),
        ];
    }
}
