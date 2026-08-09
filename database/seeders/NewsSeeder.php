<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->articles() as $index => $article) {
            News::factory()->create([
                ...$article,
                'slug' => Str::slug($article['title']),
                'created_at' => now()->subDays(($index + 1) * 7),
                'updated_at' => now()->subDays(($index + 1) * 7),
            ]);
        }

        News::factory(5)->create();
        News::factory(2)->unpublished()->create();
    }

    /**
     * @return array<int, array{title: string, summary: string, content: string, type: string}>
     */
    private function articles(): array
    {
        return [
            [
                'title' => 'Het nieuwe seizoen is geopend',
                'summary' => 'Vanaf dit weekend zijn alle plaatsen en chalets weer te boeken.',
                'content' => 'De winterstop zit erop: vanaf dit weekend is de camping weer volledig open. '
                    ."Alle kampeerplaatsen, caravanplaatsen en chalets zijn vanaf nu online te reserveren.\n\n"
                    .'Het sanitairgebouw is de afgelopen maanden gerenoveerd en het restaurant heeft een nieuwe kaart. '
                    .'Wij kijken ernaar uit om jullie weer te ontvangen.',
                'type' => 'Algemeen',
            ],
            [
                'title' => 'Vroegboekkorting: 15% op alle plaatsen',
                'summary' => 'Boek voor 1 mei en profiteer van 15% korting op je hele verblijf.',
                'content' => 'Wie zijn vakantie op tijd plant, betaalt bij ons minder. Reserveer je verblijf voor 1 mei '
                    ."en ontvang automatisch 15% korting op het totaalbedrag.\n\n"
                    .'De korting geldt voor alle plaatsen en accommodaties en wordt bij het afrekenen verrekend. '
                    .'De actie is niet te combineren met andere aanbiedingen.',
                'type' => 'Aanbieding',
            ],
            [
                'title' => 'Zomerfeest met livemuziek',
                'summary' => 'Op zaterdag 12 juli staat het campingterrein in het teken van het jaarlijkse zomerfeest.',
                'content' => 'Het zomerfeest begint om 16:00 uur met een springkussen en schminken voor de kinderen. '
                    ."Vanaf 19:00 uur speelt er een liveband op het grasveld naast het restaurant.\n\n"
                    .'Deelname is gratis voor alle gasten. Aanmelden is niet nodig, kom gewoon langs.',
                'type' => 'Evenement',
            ],
            [
                'title' => 'Onderhoud aan het sanitairgebouw',
                'summary' => 'Tussen 3 en 6 juni is het sanitairgebouw bij veld B gedeeltelijk gesloten.',
                'content' => 'Van dinsdag 3 juni tot en met vrijdag 6 juni vervangen wij de leidingen in het '
                    ."sanitairgebouw bij veld B. Het gebouw is in die periode gedeeltelijk gesloten.\n\n"
                    .'Het sanitairgebouw bij de receptie blijft de hele week gewoon open en is dag en nacht '
                    .'bereikbaar. Onze excuses voor het ongemak.',
                'type' => 'Onderhoud',
            ],
            [
                'title' => 'Nieuwe wandelroute door het achterliggende bos',
                'summary' => 'Een gemarkeerde route van 6 kilometer start vanaf de slagboom bij de receptie.',
                'content' => 'In samenwerking met de gemeente hebben wij een nieuwe wandelroute uitgezet. '
                    ."De route is 6 kilometer lang en loopt door het bos achter de camping.\n\n"
                    .'De route is gemarkeerd met gele paaltjes en start bij de slagboom naast de receptie. '
                    .'Een routekaartje is gratis op te halen bij de balie.',
                'type' => 'Algemeen',
            ],
        ];
    }
}
