<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $questions_en = [
            "Do you enjoy the interplay between control and submission in intimate settings?",
            "Are you excited by exploring the limits of pain and pleasure with a partner?",
            "Does the thought of using safe words during intense scenarios excite you?",
            "Do you crave structured power dynamics with a clear dominant and submissive?",
            "Do you enjoy the idea of being restrained with ropes, cuffs, or other tools?",
            "Does the sensation of tight bindings around your body arouse you?",
            "Are you turned on by the idea of your partner being physically immobilized?",
            "Are you excited by the idea of enforcing or following strict rules in a scene?",
            "Do you enjoy being punished or rewarding a partner for their behavior?",
            "Does the idea of correcting someone’s actions through discipline excite you?",
            "Do you feel empowered by taking control in intimate scenarios?",
            "Are you aroused by the thought of your partner submitting to your every desire?",
            "Do you enjoy being the one who sets the rules and boundaries?",
            "Do you find pleasure in surrendering control to a trusted partner?",
            "Does the thought of giving up decision-making turn you on?",
            "Do you enjoy being guided, instructed, or commanded during intimate encounters?",
            "Do you find a mix of pain and pleasure intensely exciting?",
            "Does the idea of leaving marks like bruises or scratches turn you on?",
            "Do you enjoy experimenting with different levels of pain for arousal?",
            "Are you excited by pretending to be someone else during intimate scenarios?",
            "Do you enjoy creating elaborate backstories and scenarios with a partner?",
            "Does the thought of exploring different characters and settings turn you on?",
            "Are you interested in scenarios where one partner plays a significantly older or younger role?",
            "Does the idea of dressing up as a younger or older character excite you?",
            "Are you aroused by the idea of infantilization or being treated as a child?",
            "Are you turned on by doctor/patient scenarios involving examinations?",
            "Does the use of medical instruments or props excite you?",
            "Do you enjoy the sensation of latex gloves or medical restraints?",
            "Are you excited by taking on the role of a pet (e.g., puppy, kitten) or treating someone as one?",
            "Does the use of collars, leashes, or pet-related toys arouse you?",
            "Do you enjoy the idea of training or being trained as a pet?",
            "Are you aroused by strict authority figures like teachers or mentors?",
            "Do you enjoy being disciplined for 'misbehavior' in an educational setting?",
            "Does the thought of being given 'private lessons' excite you?",
            "Do you find the sound of spanking, flogging, or caning to be arousing?",
            "Are you turned on by experimenting with different tools for impact play?",
            "Do you enjoy administering or receiving hits to mix pain with pleasure?",
            "Do you enjoy limiting your partner’s senses to heighten other sensations?",
            "Are you excited by the idea of losing control of your senses?",
            "Are you turned on by blindfolding or gagging yourself or your partner?",
            "Are you interested in experimenting with hot wax or ice for stimulation?",
            "Does the contrast of hot and cold sensations excite you during intimate moments?",
            "Do you enjoy testing your or your partner's sensitivity to different temperatures?",
            "Are you turned on by light, teasing touches using feathers or soft objects?",
            "Do you enjoy making your partner squirm with gentle, ticklish sensations?",
            "Are you excited by the idea of using tickling as a form of foreplay?",
            "Are you intrigued by the idea of using electrical currents on your body?",
            "Do you enjoy experimenting with mild electro-stimulation for arousal?",
            "Does the thought of controlling or receiving controlled shocks excite you?",
            "Do you find the idea of being treated purely as an object arousing?",
            "Are you turned on by having your partner ignore your humanity for the sake of pleasure?",
            "Are you excited by scenarios where your purpose is to serve a single function?",
            "Do you enjoy the idea of being dressed up and posed like a lifeless doll?",
            "Are you turned on by scenarios where you have no control over your movements?",
            "Does the idea of transforming into or controlling a doll-like partner excite you?",
            "Are you turned on by the idea of completely surrendering your will to a partner?",
            "Does the thought of owning or being owned by someone excite you?",
            "Are you excited by exploring extreme forms of servitude or obedience?",
            "Are you turned on by the sound of balloons being inflated or popped?",
            "Does the idea of incorporating balloons into intimate play excite you?",
            "Do you find the sensation of rubbing or pressing against balloons exciting?",
            "Do you enjoy the tight, shiny look and feel of latex clothing or accessories?",
            "Are you aroused by the sensation of latex against your skin?",
            "Does the smell or texture of latex excite you during intimate encounters?",
            "Do you find it arousing when a partner talks explicitly during intimate moments?",
            "Does the idea of describing fantasies or scenarios verbally turn you on?",
            "Are you excited by being called names or using degrading language consensually?",
            "Are you excited by the thought of your partner being intimate with someone else?",
            "Do you enjoy watching your partner with another person while you are present?",
            "Does the thought of sharing your partner for their pleasure excite you?",
            "Are you interested in exploring the sensation of ginger root for stimulation?",
            "Does the idea of intense, tingling sensations in intimate areas excite you?",
            "Are you excited by the idea of being watched while being intimate?",
            "Do you find pleasure in the risk of being caught in public scenarios?",
            "Are you turned on by undressing or being exposed in front of an audience?",
            "Are you aroused by the thought of a partner willingly giving you money or gifts?",
            "Does the idea of using financial control to assert power over someone excite you?",
            "Are you excited by the sight, smell, or touch of feet?",
            "Does the idea of using feet during intimate play excite you?",
            "Do you find it arousing to massage, kiss, or worship a partner's feet?",
            "Does the idea of incorporating urine into intimate play excite you?",
            "Are you turned on by the act of being urinated on or doing it to someone else?",
            "Are you aroused by secretly watching others being intimate?",
            "Does the thrill of observing someone without their knowledge excite you?",
            "Are you interested in scenarios where you’re a passive observer of intimacy?",
            "Are you turned on by the idea of using needles for controlled, consensual pain?",
            "Do you enjoy the sight of needles piercing skin for aesthetic or sensory pleasure?",
            "Are you interested in exploring temporary piercings as part of intimacy?"
        ];
        $questions_de = [
            "Genießt du das Zusammenspiel von Kontrolle und Hingabe in intimen Momenten?",
            "Reizt es dich, mit einem Partner die Grenzen von Schmerz und Lust auszuloten?",
            "Erregt dich der Gedanke, in intensiven Szenarien mit Safewords zu arbeiten?",
            "Verlangst du nach klaren Machtstrukturen mit einer eindeutigen dominanten und einer unterwürfigen Rolle?",
            "Gefällt dir der Gedanke, mit Seilen, Handschellen oder anderen Hilfsmitteln fixiert zu werden?",
            "Erregt dich das Gefühl enger Fesselungen um deinen Körper?",
            "Turnt dich der Gedanke an, dass dein Partner körperlich bewegungsunfähig ist?",
            "Reizt dich die Idee, in einer Szene strikte Regeln durchzusetzen oder zu befolgen?",
            "Genießt du es, bestraft zu werden oder einen Partner für sein Verhalten zu belohnen?",
            "Erregt dich der Gedanke, das Verhalten anderer durch Disziplin zu korrigieren?",
            "Fühlst du dich ermächtigt, in intimen Momenten die Kontrolle zu übernehmen?",
            "Macht dich der Gedanke an, dass dein Partner sich deinen Wünschen vollkommen hingibt?",
            "Genießt du es, die Regeln und Grenzen festzulegen?",
            "Findest du Freude daran, einem vertrauenswürdigen Partner die Kontrolle zu überlassen?",
            "Turnt dich der Gedanke an, die Entscheidungsgewalt abzugeben?",
            "Magst du es, während intimer Begegnungen geführt, angewiesen oder kommandiert zu werden?",
            "Findest du eine Mischung aus Schmerz und Lust besonders aufregend?",
            "Reizt dich der Gedanke, Spuren wie blaue Flecken oder Kratzer zu hinterlassen?",
            "Experimentierst du gerne mit verschiedenen Schmerzgrenzen zur Erregung?",
            "Erregt dich die Vorstellung, während intimer Momente jemand anderes zu sein?",
            "Genießt du es, mit einem Partner ausgeklügelte Hintergrundgeschichten und Szenarien zu erschaffen?",
            "Reizt dich der Gedanke, verschiedene Charaktere und Umgebungen zu erkunden?",
            "Interessieren dich Szenarien, in denen ein Partner eine deutlich ältere oder jüngere Rolle spielt?",
            "Erregt dich die Vorstellung, dich als jüngeren oder älteren Charakter zu verkleiden?",
            "Turnt dich die Idee an, infantilisiert oder wie ein Kind behandelt zu werden?",
            "Macht dich der Gedanke an Arzt-Patient-Szenarien mit Untersuchungen an?",
            "Reizt dich der Einsatz von medizinischen Instrumenten oder Requisiten?",
            "Gefällt dir das Gefühl von Latexhandschuhen oder medizinischen Fesseln?",
            "Erregt dich die Vorstellung, in einer Rolle als Haustier (z.B. Hund, Katze) zu sein oder jemanden als solches zu behandeln?",
            "Turnt dich der Einsatz von Halsbändern, Leinen oder haustierbezogenen Spielzeugen?",
            "Genießt du die Idee, wie ein Haustier trainiert oder trainiert zu werden?",
            "Erregt dich die Vorstellung von strengen Autoritätsfiguren wie Lehrern oder Mentoren?",
            "Genießt du es, für 'Fehlverhalten' in einem Bildungskontext bestraft zu werden?",
            "Reizt dich der Gedanke, 'Privatstunden' zu erhalten?",
            "Findest du das Geräusch von Klapsen, Peitschen oder Streichen erregend?",
            "Erregt dich das Experimentieren mit verschiedenen Werkzeugen für Impact Play?",
            "Genießt du es, Schläge zu verabreichen oder zu empfangen, um Schmerz mit Lust zu mischen?",
            "Magst du es, die Sinne deines Partners zu begrenzen, um andere Empfindungen zu steigern?",
            "Reizt dich der Gedanke, die Kontrolle über deine Sinne zu verlieren?",
            "Turnt dich der Gedanke an, dich oder deinen Partner zu verbinden oder zu knebeln?",
            "Interessierst du dich dafür, mit heißem Wachs oder Eis zur Stimulation zu experimentieren?",
            "Erregt dich der Gegensatz von heißen und kalten Empfindungen während intimer Momente?",
            "Genießt du es, die Empfindlichkeit deines Partners auf verschiedene Temperaturen zu testen?",
            "Erregt es dich, sanfte, kitzelnde Berührungen mit Federn oder weichen Objekten zu spüren?",
            "Magst du es, deinen Partner mit sanften, kitzeligen Empfindungen zum Zucken zu bringen?",
            "Reizt dich die Vorstellung, Kitzeln als Teil des Vorspiels zu verwenden?",
            "Interessierst du dich für die Idee, elektrische Ströme auf deinen Körper anzuwenden?",
            "Erregt dich das Experimentieren mit milden Elektro-Stimulationen zur Erregung?",
            "Reizt dich der Gedanke, kontrollierte oder empfangene Schocks zu erleben?",
            "Findest du die Vorstellung, als Objekt behandelt zu werden, erregend?",
            "Erregt dich der Gedanke, dass dein Partner deine Menschlichkeit zugunsten des Vergnügens ignoriert?",
            "Reizt dich die Vorstellung, dass dein einziger Zweck darin besteht, einem Partner zu dienen?",
            "Genießt du die Idee, dich zu verkleiden und wie eine leblose Puppe positioniert zu werden?",
            "Turnt dich der Gedanke an, Szenarien zu erleben, in denen du keine Kontrolle über deine Bewegungen hast?",
            "Erregt es dich, deinen Partner in eine ähnliche puppenartige Rolle zu verwandeln?",
            "Findest du es erregend, völlig deinen Willen einem Partner zu überlassen?",
            "Reizt dich der Gedanke, von jemandem besessen oder als Besitz betrachtet zu werden?",
            "Interessierst du dich für extreme Formen der Knechtschaft oder des Gehorsams?",
            "Erregt es dich, den Klang von Ballons zu hören, die aufgeblasen oder zum Platzen gebracht werden?",
            "Findest du die Vorstellung, mit Ballons in intimen Momenten zu spielen, erregend?",
            "Reizt es dich, das Gefühl von Ballons zu spüren, die gegen deinen Körper gedrückt werden?",
            "Magst du das glänzende Aussehen und Gefühl von Latexkleidung oder -accessoires?",
            "Erregt dich das Gefühl von Latex auf deiner Haut?",
            "Turnt dich der Geruch oder die Textur von Latex während intimer Begegnungen?",
            "Findest du es erregend, wenn ein Partner explizit während intimer Momente spricht?",
            "Reizt es dich, Fantasien oder Szenarien verbal zu beschreiben?",
            "Erregt es dich, von einem Partner mit abwertenden oder erniedrigenden Begriffen angesprochen zu werden?",
            "Erregt es dich, die Vorstellung, dass dein Partner mit jemand anderem intim wird?",
            "Genießt du es, deinen Partner mit einer anderen Person zu beobachten, während du anwesend bist?",
            "Reizt es dich, deinen Partner zu teilen, damit er/sie Freude daran hat?",
            "Interessierst du dich für die Verwendung von Ingwerwurzel zur Stimulation?",
            "Erregt es dich, intensive, prickelnde Empfindungen in intimen Bereichen zu erleben?",
            "Turnt es dich an, beobachtet zu werden, während du intim bist?",
            "Findest du den Nervenkitzel aufregend, in der Öffentlichkeit beim Intimsein erwischt zu werden?",
            "Reizt es dich, sich auszuziehen oder in der Öffentlichkeit exponiert zu sein?",
            "Erregt es dich, von einem Partner Geld oder Geschenke zu erhalten?",
            "Reizt es dich, finanzielle Kontrolle zu nutzen, um Macht über jemanden auszuüben?",
            "Erregt es dich, die Füße zu sehen, zu riechen oder zu berühren?",
            "Findest du es erregend, mit den Füßen zu spielen?",
            "Magst du es, die Füße eines Partners zu massieren, zu küssen oder zu verehren?",
            "Erregt es dich, die Vorstellung von Urin in intimen Momenten zu integrieren?",
            "Findest du es erregend, uriniert zu werden oder es einem anderen zu tun?",
            "Erregt es dich, heimlich anderen beim Intimsein zuzusehen?",
            "Findest du den Nervenkitzel aufregend, jemandem ohne dessen Wissen zuzusehen?",
            "Interessierst du dich für Szenarien, in denen du ein passiver Beobachter der Intimität bist?",
            "Reizt es dich, die Vorstellung, mit Nadeln kontrolliert und einvernehmlich Schmerz zu erfahren?",
            "Erregt es dich, wenn Nadeln in die Haut eindringen, um ästhetische oder sensorische Lust zu erzeugen?",
            "Interessierst du dich für temporäre Piercings im Rahmen von Intimität?"
        ];


        // echo 'Count of questions_en: ' . count($questions_en);

        // echo 'Count of questions_de: ' . count($questions_de);


       // Combine English and German questions
        $questions = [];
        for ($i = 0; $i < count($questions_en); $i++) {
            $questions[] = [
                'en' => $questions_en[$i],
                'de' => $questions_de[$i],
            ];
        }

        // Insert questions and their translations
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
                    // 'hint' => null, // Optional: Add hints if needed
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'question_id' => $questionId,
                    'language' => 'de',
                    'text' => $question['de'],
                    // 'hint' => null, // Optional: Add hints if needed
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}
