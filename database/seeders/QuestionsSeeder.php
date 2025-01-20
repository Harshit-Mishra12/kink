<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // List of questions in English and their German translations
        $questions = [
            [
                'en' => 'Do you enjoy the interplay between control and submission in intimate settings?',
                'de' => 'Genießen Sie das Zusammenspiel von Kontrolle und Unterwerfung in intimen Situationen?',
            ],
            [
                'en' => 'Are you excited by exploring the limits of pain and pleasure with a partner?',
                'de' => 'Reizt es Sie, die Grenzen von Schmerz und Vergnügen mit einem Partner zu erkunden?',
            ],
            [
                'en' => 'Does the thought of using safe words during intense scenarios excite you?',
                'de' => 'Erregt Sie der Gedanke, in intensiven Szenarien sichere Worte zu verwenden?',
            ],
            [
                'en' => 'Do you crave structured power dynamics with a clear dominant and submissive?',
                'de' => 'Sehnen Sie sich nach klar definierten Machtverhältnissen mit einem dominanten und einem unterwürfigen Part?',
            ],
            [
                'en' => 'Do you enjoy the idea of being restrained with ropes, cuffs, or other tools?',
                'de' => 'Mögen Sie die Vorstellung, mit Seilen, Handschellen oder anderen Werkzeugen gefesselt zu werden?',
            ],
            [
                'en' => 'Does the sensation of tight bindings around your body arouse you?',
                'de' => 'Erregt Sie das Gefühl enger Fesseln um Ihren Körper?',
            ],
            [
                'en' => 'Are you turned on by the idea of your partner being physically immobilized?',
                'de' => 'Reizt Sie die Idee, dass Ihr Partner körperlich bewegungsunfähig gemacht wird?',
            ],
            [
                'en' => 'Are you excited by the idea of enforcing or following strict rules in a scene?',
                'de' => 'Erregt Sie die Idee, strenge Regeln in einer Szene durchzusetzen oder zu befolgen?',
            ],
            [
                'en' => 'Do you enjoy being punished or rewarding a partner for their behavior?',
                'de' => 'Mögen Sie es, Ihren Partner für sein Verhalten zu bestrafen oder zu belohnen?',
            ],
            [
                'en' => 'Does the idea of correcting someone’s actions through discipline excite you?',
                'de' => 'Erregt Sie die Vorstellung, die Handlungen eines anderen durch Disziplin zu korrigieren?',
            ],
            [
                'en' => 'Do you feel empowered by taking control in intimate scenarios?',
                'de' => 'Fühlen Sie sich gestärkt, wenn Sie in intimen Szenarien die Kontrolle übernehmen?',
            ],
            [
                'en' => 'Are you aroused by the thought of your partner submitting to your every desire?',
                'de' => 'Erregt Sie der Gedanke, dass sich Ihr Partner all Ihren Wünschen unterwirft?',
            ],
            [
                'en' => 'Do you enjoy being the one who sets the rules and boundaries?',
                'de' => 'Mögen Sie es, derjenige zu sein, der die Regeln und Grenzen festlegt?',
            ],
            [
                'en' => 'Do you find pleasure in surrendering control to a trusted partner?',
                'de' => 'Finden Sie Freude daran, die Kontrolle an einen vertrauenswürdigen Partner abzugeben?',
            ],
            [
                'en' => 'Does the thought of giving up decision-making turn you on?',
                'de' => 'Erregt Sie der Gedanke, die Entscheidungsfindung abzugeben?',
            ],
            [
                'en' => 'Do you enjoy being guided, instructed, or commanded during intimate encounters?',
                'de' => 'Mögen Sie es, während intimer Begegnungen geführt, instruiert oder kommandiert zu werden?',
            ],
            [
                'en' => 'Do you find a mix of pain and pleasure intensely exciting?',
                'de' => 'Finden Sie eine Mischung aus Schmerz und Vergnügen äußerst aufregend?',
            ],
            [
                'en' => 'Does the idea of leaving marks like bruises or scratches turn you on?',
                'de' => 'Erregt Sie die Vorstellung, Spuren wie Blutergüsse oder Kratzer zu hinterlassen?',
            ],
            [
                'en' => 'Do you enjoy experimenting with different levels of pain for arousal?',
                'de' => 'Experimentieren Sie gerne mit unterschiedlichen Schmerzgraden zur Erregung?',
            ],
            [
                'en' => 'Are you excited by pretending to be someone else during intimate scenarios?',
                'de' => 'Erregt Sie der Gedanke, während intimer Szenarien jemand anderes zu sein?',
            ],
            [
                'en' => 'Do you enjoy creating elaborate backstories and scenarios with a partner?',
                'de' => 'Mögen Sie es, mit einem Partner ausgeklügelte Hintergrundgeschichten und Szenarien zu erstellen?',
            ],
            [
                'en' => 'Does the thought of exploring different characters and settings turn you on?',
                'de' => 'Erregt Sie die Vorstellung, verschiedene Charaktere und Umgebungen zu erkunden?',
            ],
            [
                'en' => 'Are you interested in scenarios where one partner plays a significantly older or younger role?',
                'de' => 'Interessieren Sie sich für Szenarien, in denen ein Partner eine deutlich ältere oder jüngere Rolle spielt?',
            ],
            // Continue this pattern for all remaining questions...
        ];

        foreach ($questions as $question) {
            // Insert into the questions table
            $questionId = DB::table('questions')->insertGetId([
                'is_active' => true, // Set the initial status as active
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert translations into the question_translations table
            DB::table('question_translations')->insert([
                [
                    'question_id' => $questionId,
                    'language' => 'en',
                    'text' => $question['en'],
                    'hint' => null, // Optional: Add hints if needed
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'question_id' => $questionId,
                    'language' => 'de',
                    'text' => $question['de'],
                    'hint' => null, // Optional: Add hints if needed
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}
