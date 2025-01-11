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
        // Insert all questions as provided in the text
        $questions = [
            'Do you enjoy the interplay between control and submission in intimate settings?',
            'Are you excited by exploring the limits of pain and pleasure with a partner?',
            'Does the thought of using safe words during intense scenarios excite you?',
            'Do you crave structured power dynamics with a clear dominant and submissive?',
            'Do you enjoy the idea of being restrained with ropes, cuffs, or other tools?',
            'Does the sensation of tight bindings around your body arouse you?',
            'Are you turned on by the idea of your partner being physically immobilized?',
            'Are you excited by the idea of enforcing or following strict rules in a scene?',
            'Do you enjoy being punished or rewarding a partner for their behavior?',
            'Does the idea of correcting someone’s actions through discipline excite you?',
            'Do you feel empowered by taking control in intimate scenarios?',
            'Are you aroused by the thought of your partner submitting to your every desire?',
            'Do you enjoy being the one who sets the rules and boundaries?',
            'Do you find pleasure in surrendering control to a trusted partner?',
            'Does the thought of giving up decision-making turn you on?',
            'Do you enjoy being guided, instructed, or commanded during intimate encounters?',
            'Do you find a mix of pain and pleasure intensely exciting?',
            'Does the idea of leaving marks like bruises or scratches turn you on?',
            'Do you enjoy experimenting with different levels of pain for arousal?',
            'Are you excited by pretending to be someone else during intimate scenarios?',
            'Do you enjoy creating elaborate backstories and scenarios with a partner?',
            'Does the thought of exploring different characters and settings turn you on?',
            'Are you interested in scenarios where one partner plays a significantly older or younger role?',
            'Does the idea of dressing up as a younger or older character excite you?',
            'Are you aroused by the idea of infantilization or being treated as a child?',
            'Are you turned on by doctor/patient scenarios involving examinations?',
            'Does the use of medical instruments or props excite you?',
            'Do you enjoy the sensation of latex gloves or medical restraints?',
            'Are you excited by taking on the role of a pet (e.g., puppy, kitten) or treating someone as one?',
            'Does the use of collars, leashes, or pet-related toys arouse you?',
            'Do you enjoy the idea of training or being trained as a pet?',
            'Are you aroused by strict authority figures like teachers or mentors?',
            'Do you enjoy being disciplined for "misbehavior" in an educational setting?',
            'Does the thought of being given "private lessons" excite you?',
            'Do you find the sound of spanking, flogging, or caning to be arousing?',
            'Are you turned on by experimenting with different tools for impact play?',
            'Do you enjoy administering or receiving hits to mix pain with pleasure?',
            'Do you enjoy limiting your partner’s senses to heighten other sensations?',
            'Are you excited by the idea of losing control of your senses?',
            'Are you turned on by blindfolding or gagging yourself or your partner?',
            'Are you interested in experimenting with hot wax or ice for stimulation?',
            'Does the contrast of hot and cold sensations excite you during intimate moments?',
            'Do you enjoy testing your or your partner\'s sensitivity to different temperatures?',
            'Are you turned on by light, teasing touches using feathers or soft objects?',
            'Do you enjoy making your partner squirm with gentle, ticklish sensations?',
            'Are you excited by the idea of using tickling as a form of foreplay?',
            'Are you intrigued by the idea of using electrical currents on your body?',
            'Do you enjoy experimenting with mild electro-stimulation for arousal?',
            'Does the thought of controlling or receiving controlled shocks excite you?',
            'Do you find the idea of being treated purely as an object arousing?',
            'Are you turned on by having your partner ignore your humanity for the sake of pleasure?',
            'Are you excited by scenarios where your purpose is to serve a single function?',
            'Do you enjoy the idea of being dressed up and posed like a lifeless doll?',
            'Are you turned on by scenarios where you have no control over your movements?',
            'Does the idea of transforming into or controlling a doll-like partner excite you?',
            'Are you turned on by the idea of completely surrendering your will to a partner?',
            'Does the thought of owning or being owned by someone excite you?',
            'Are you excited by exploring extreme forms of servitude or obedience?',
            'Are you turned on by the sound of balloons being inflated or popped?',
            'Does the idea of incorporating balloons into intimate play excite you?',
            'Do you find the sensation of rubbing or pressing against balloons exciting?',
            'Do you enjoy the tight, shiny look and feel of latex clothing or accessories?',
            'Are you aroused by the sensation of latex against your skin?',
            'Does the smell or texture of latex excite you during intimate encounters?',
            'Do you find it arousing when a partner talks explicitly during intimate moments?',
            'Does the idea of describing fantasies or scenarios verbally turn you on?',
            'Are you excited by being called names or using degrading language consensually?',
            'Are you excited by the thought of your partner being intimate with someone else?',
            'Do you enjoy watching your partner with another person while you are present?',
            'Does the thought of sharing your partner for their pleasure excite you?',
            'Are you interested in exploring the sensation of ginger root for stimulation?',
            'Does the idea of intense, tingling sensations in intimate areas excite you?',
            'Are you excited by the idea of being watched while being intimate?',
            'Do you find pleasure in the risk of being caught in public scenarios?',
            'Are you turned on by undressing or being exposed in front of an audience?',
            'Are you aroused by the thought of a partner willingly giving you money or gifts?',
            'Does the idea of using financial control to assert power over someone excite you?',
            'Are you excited by the sight, smell, or touch of feet?',
            'Does the idea of using feet during intimate play excite you?',
            'Do you find it arousing to massage, kiss, or worship a partner\'s feet?',
            'Does the idea of incorporating urine into intimate play excite you?',
            'Are you turned on by the act of being urinated on or doing it to someone else?',
            'Are you aroused by secretly watching others being intimate?',
            'Does the thrill of observing someone without their knowledge excite you?',
            'Are you interested in scenarios where you’re a passive observer of intimacy?',
            'Are you turned on by the idea of using needles for controlled, consensual pain?',
            'Do you enjoy the sight of needles piercing skin for aesthetic or sensory pleasure?',
            'Are you interested in exploring temporary piercings as part of intimacy?'
        ];

        // Loop through and insert each question into the database with status 'active'
        foreach ($questions as $question) {
            DB::table('questions')->insert([
                'question_text' => $question,
                'status' => 'active', // Set the initial status as 'active'
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
