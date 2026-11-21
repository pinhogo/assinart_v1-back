<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\FeatureLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureLeadTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_creates_vitrine_lead_with_valid_email(): void
    {
        $response = $this->postJson('/api/v1/feature-leads', [
            'feature' => 'vitrine',
            'source' => 'home_hero',
            'contact_value' => '  Artista@Exemplo.COM  ',
            'metadata' => [
                'selling_type' => 'prints',
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'artist_id' => null,
                    'feature' => 'vitrine',
                    'source' => 'home_hero',
                    'contact_value' => 'artista@exemplo.com',
                    'metadata' => [
                        'selling_type' => 'prints',
                    ],
                    'already_registered' => false,
                ],
            ]);

        $this->assertDatabaseHas('feature_leads', [
            'artist_id' => null,
            'feature' => 'vitrine',
            'source' => 'home_hero',
            'contact_value' => 'artista@exemplo.com',
        ]);
    }

    public function test_guest_creates_vitrine_lead_with_valid_handle_normalized(): void
    {
        $response = $this->postJson('/api/v1/feature-leads', [
            'source' => 'home_waitlist',
            'contact_value' => '  @Artista.Visual_01  ',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'artist_id' => null,
                    'feature' => 'vitrine',
                    'source' => 'home_waitlist',
                    'contact_value' => '@artista.visual_01',
                    'already_registered' => false,
                ],
            ]);

        $this->assertDatabaseHas('feature_leads', [
            'artist_id' => null,
            'feature' => 'vitrine',
            'contact_value' => '@artista.visual_01',
        ]);
    }

    public function test_guest_resubmitting_same_contact_and_feature_returns_200_and_updates_metadata(): void
    {
        $this->postJson('/api/v1/feature-leads', [
            'feature' => 'vitrine',
            'source' => 'home_hero',
            'contact_value' => 'duplicado@exemplo.com',
            'metadata' => [
                'selling_type' => 'prints',
            ],
        ])->assertStatus(201);

        $secondResponse = $this->postJson('/api/v1/feature-leads', [
            'feature' => 'vitrine',
            'source' => 'home_footer',
            'contact_value' => 'DUPLICADO@exemplo.com',
            'metadata' => [
                'selling_types' => ['originais', 'comissoes'],
            ],
        ]);

        $secondResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'artist_id' => null,
                    'feature' => 'vitrine',
                    'source' => 'home_footer',
                    'contact_value' => 'duplicado@exemplo.com',
                    'metadata' => [
                        'selling_types' => ['originais', 'comissoes'],
                    ],
                    'already_registered' => true,
                ],
            ]);

        $this->assertDatabaseCount('feature_leads', 1);
    }

    public function test_guest_without_contact_or_with_invalid_or_html_contact_returns_422(): void
    {
        $this->postJson('/api/v1/feature-leads', [
            'feature' => 'vitrine',
            'source' => 'home_hero',
        ])->assertStatus(422)->assertJsonValidationErrors(['contact_value']);

        $this->postJson('/api/v1/feature-leads', [
            'feature' => 'vitrine',
            'source' => 'home_hero',
            'contact_value' => 'contato invalido com espacos',
        ])->assertStatus(422)->assertJsonValidationErrors(['contact_value']);

        $this->postJson('/api/v1/feature-leads', [
            'feature' => 'vitrine',
            'source' => 'home_hero',
            'contact_value' => '<script><b></b></script>',
        ])->assertStatus(422)->assertJsonValidationErrors(['contact_value']);
    }

    public function test_authenticated_artist_submits_editor_lead_without_contact_and_updates_on_retry(): void
    {
        $artist = Artist::factory()->create([
            'email' => 'criadora@assin.art',
        ]);

        $firstResponse = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/feature-leads', [
                'feature' => 'vitrine',
                'source' => 'editor_banner',
                'metadata' => [
                    'selling_type' => 'originais',
                ],
            ]);

        $firstResponse->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'artist_id' => $artist->id,
                    'feature' => 'vitrine',
                    'source' => 'editor_banner',
                    'contact_value' => 'criadora@assin.art',
                    'metadata' => [
                        'selling_type' => 'originais',
                    ],
                    'already_registered' => false,
                ],
            ]);

        $secondResponse = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/feature-leads', [
                'feature' => 'vitrine',
                'source' => 'editor_banner',
                'metadata' => [
                    'selling_types' => ['prints', 'comissoes'],
                ],
            ]);

        $secondResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'artist_id' => $artist->id,
                    'contact_value' => 'criadora@assin.art',
                    'metadata' => [
                        'selling_types' => ['prints', 'comissoes'],
                    ],
                    'already_registered' => true,
                ],
            ]);

        $this->assertDatabaseCount('feature_leads', 1);
    }

    public function test_feature_lead_status_requires_auth_and_reflects_registration(): void
    {
        $this->getJson('/api/v1/feature-leads/status?feature=vitrine')
            ->assertStatus(401);

        $artist = Artist::factory()->create([
            'email' => 'status@assin.art',
        ]);

        $this->actingAs($artist, 'sanctum')
            ->getJson('/api/v1/feature-leads/status?feature=vitrine')
            ->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'registered' => false,
                    'lead' => null,
                ],
            ]);

        $lead = FeatureLead::create([
            'artist_id' => $artist->id,
            'feature' => 'vitrine',
            'source' => 'editor_banner',
            'contact_value' => $artist->email,
            'metadata' => ['selling_type' => 'prints'],
        ]);

        $this->actingAs($artist, 'sanctum')
            ->getJson('/api/v1/feature-leads/status?feature=vitrine')
            ->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'registered' => true,
                    'lead' => [
                        'id' => $lead->id,
                        'artist_id' => $artist->id,
                        'feature' => 'vitrine',
                        'source' => 'editor_banner',
                        'contact_value' => 'status@assin.art',
                    ],
                ],
            ]);
    }

    public function test_track_funnel_records_allowed_events_without_page_views_and_rejects_invalid_event(): void
    {
        $allowedEvents = [
            'home_pageview',
            'home_waitlist_vitrine_submitted',
            'editor_session',
            'editor_vitrine_cta_clicked',
        ];

        foreach ($allowedEvents as $index => $eventName) {
            $this->postJson('/api/v1/track', [
                'type' => 'funnel',
                'event_name' => $eventName,
                'visitor_id' => 'visitor-' . $index,
                'source' => 'home_hero',
                'metadata' => ['step' => $index],
            ])->assertStatus(204);

            $this->assertDatabaseHas('funnel_events', [
                'event_name' => $eventName,
                'visitor_id' => 'visitor-' . $index,
                'source' => 'home_hero',
            ]);
        }

        $this->assertDatabaseCount('funnel_events', 4);
        $this->assertDatabaseCount('page_views', 0);

        $this->postJson('/api/v1/track', [
            'type' => 'funnel',
            'event_name' => 'unknown_funnel_event',
            'visitor_id' => 'visitor-invalid',
        ])->assertStatus(422)->assertJsonValidationErrors(['event_name']);
    }
}
