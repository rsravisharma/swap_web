<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PdfBook;
use App\Models\User;
use Faker\Factory as Faker;

class PdfBookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Get all user IDs who can be sellers
        $sellerIds = User::pluck('id')->toArray();

        if (empty($sellerIds)) {
            $this->command->warn('No users found. Please seed users first.');
            return;
        }

        // Sample Google Drive file IDs (replace with your actual file IDs)
        $sampleDriveFiles = [
            [
                'file_id' => '1u8emOvAYI85JQg184K0RU_IYh6jmdO6T',
                'shareable_link' => 'https://drive.google.com/file/d/1u8emOvAYI85JQg184K0RU_IYh6jmdO6T/view?usp=sharing'
            ],
            [
                'file_id' => '1SImIS8yPmjZOtQiLAYlkrO0v-UBtfqNX',
                'shareable_link' => 'https://drive.google.com/file/d/1SImIS8yPmjZOtQiLAYlkrO0v-UBtfqNX/view?usp=sharing'
            ],
            [
                'file_id' => '1MzDVOIVQsRueSubr78Z4zuu7f0Wsvw6G',
                'shareable_link' => 'https://drive.google.com/file/d/1MzDVOIVQsRueSubr78Z4zuu7f0Wsvw6G/view?usp=sharing'
            ],
            // [
            //     'file_id' => '1h1Pjea0CwZTpmSp0F1T6vmB5hZ-EAN6P',
            //     'shareable_link' => 'https://drive.google.com/file/d/1h1Pjea0CwZTpmSp0F1T6vmB5hZ-EAN6P/view?usp=sharing'
            // ],
            [
                'file_id' => '12P4o1zncd2FmCUDZrrYhqlE1gW7pYVgM',
                'shareable_link' => 'https://drive.google.com/file/d/12P4o1zncd2FmCUDZrrYhqlE1gW7pYVgM/view?usp=sharing'
            ],
            [
                'file_id' => '1_WXJsOav47AqT8TyUPsXSFY2Tt6iZlsP',
                'shareable_link' => 'https://drive.google.com/file/d/1_WXJsOav47AqT8TyUPsXSFY2Tt6iZlsP/view?usp=sharing'
            ],
        ];

        // Popular programming/tech book titles for realistic data
        $bookTemplates = [
            [
                'title' => 'The 7 Habits of Highly Effective People',
                'author' => 'Stephen R. Covey',
                'isbn' => '0743269519',
                'publisher' => 'Free Press',
                'year' => 1989,
                'pages' => 381,
                'file_size' => 687104,
                'seller_id' => 1,
                'description' => 'A seminal work in personal development, this book presents a principle-centered approach to solving personal and professional problems. Covey reveals a step-by-step pathway for living with fairness, integrity, service, and human dignity—principles that give us the security to adapt to change and the wisdom to take advantage of the opportunities that change creates. By moving from dependence to independence and ultimately to interdependence, readers learn to master self-leadership and collaborative success.',
                'cover_image' => 'pdf_books/user_1/The_7_Habits_of_Highly_Effective_People.jpg',
                'price' => 20,
            ],
            [
                'title' => 'Atomic Habits',
                'author' => 'James Clear',
                'isbn' => '9780735211292',
                'publisher' => 'Avery',
                'year' => 2018,
                'pages' => 320,
                'file_size' => 5138022,
                'seller_id' => 1,
                'description' => 'Atomic Habits offers a proven framework for improving every day. James Clear, one of the world’s leading experts on habit formation, reveals practical strategies that will teach you exactly how to form good habits, break bad ones, and master the tiny behaviors that lead to remarkable results. If you’re having trouble changing your habits, the problem isn’t you; the problem is your system. Bad habits repeat themselves again and again not because you don’t want to change, but because you have the wrong system for change. You do not rise to the level of your goals; you fall to the level of your systems. In this book, you’ll get a proven plan that can take you to new heights.Clear is known for his ability to distill complex topics into simple behaviors that can be easily applied to daily life and work. Here, he draws on the most proven ideas from biology, psychology, and neuroscience to create an easy-to-understand guide for making good habits inevitable and bad habits impossible.',
                'cover_image' => 'pdf_books/user_1/Atomic_Habits_book_cover.jpg',
                'price' => 27,
            ],
            [
                'title' => 'Brave New World',
                'author' => 'Aldous Huxley',
                'isbn' => '9780060850524',
                'publisher' => 'Harper Perennial',
                'year' => 1932,
                'pages' => 288,
                'file_size' => 593920,
                'seller_id' => 1,
                'description' => 'Aldous Huxley’s profoundly important classic of world literature is a searching vision of an unequal, technologically-advanced future where humans are genetically bred, socially indoctrinated, and pharmaceutically anesthetized to passively uphold an authoritarian ruling order. Set in London in the year AF 632 (After Ford), the novel anticipates huge scientific advances in reproductive technology, sleep-learning, and psychological manipulation. The story follows Bernard Marx, an alpha-plus who feels like an outsider in a world where everyone is "happy" but no one is free. His journey to a "Savage Reservation" brings him into contact with John the Savage, whose arrival in the "civilized" world triggers a collision between human emotion and a society designed for mindless consumption and stability. Often compared to Orwell’s 1984, Brave New World offers a chillingly relevant warning about a world where people are controlled not by pain, but by pleasure. It remains a cornerstone of dystopian fiction, exploring themes of individuality, the dangers of state control, and the true cost of a painless existence.',
                'cover_image' => 'pdf_books/user_1/BraveNewWorld_FirstEdition.jpg',
                'price' => 18,
            ],
            // [
            //     'title' => 'Crime and Punishment',
            //     'author' => 'Fyodor Dostoevsky',
            //     'isbn' => '9780140449136',
            //     'publisher' => 'Penguin Classics',
            //     'year' => 1866,
            //     'pages' => 576,
            //     'file_size' => 42781900,
            //     'seller_id' => 1,
            //     'description' => 'Crime and Punishment is a psychological masterpiece that follows the mental anguish and moral dilemmas of Rodion Raskolnikov, an impoverished ex-student in Saint Petersburg. Raskolnikov formulates a plan to kill an unscrupulous pawnbroker for her money, arguing that with the riches he can perform good deeds to counterbalance his crime. The novel delves deep into the protagonist’s descent into madness and his subsequent search for redemption. As the police investigator Porfiry Petrovich closes in, Raskolnikov finds himself caught in a web of his own guilt and the spiritual influence of Sonya, a self-sacrificing young woman who becomes his moral compass. Dostoevsky explores themes of alienation, nihilism, and the possibility of spiritual rebirth through suffering. It remains one of the most influential works of world literature, offering a profound examination of the human condition and the ethical consequences of one\'s actions.',
            //     'cover_image' => 'https://picsum.photos/400/600?random=' . rand(1, 1000),
            //     'price' => 15,
            // ],
            [
                'title' => 'Don\'t Believe Everything You Think',
                'author' => 'Joseph Nguyen',
                'isbn' => '9798427063852',
                'publisher' => 'Independently Published',
                'year' => 2022,
                'pages' => 192,
                'file_size' => 496640,
                'seller_id' => 1,
                'description' => 'This book offers a simple yet revolutionary perspective on how to stop the cycle of overthinking and suffering. Joseph Nguyen explains that our pain doesn’t come from our circumstances, but from our thinking about them. By understanding the nature of thought, readers can find a way to access their innate peace and intuition. The text is designed to help those struggling with anxiety, self-doubt, and self-sabotage by showing that we are not our thoughts, but the witness of them. It provides a pathway to let go of the "mental noise" that prevents us from experiencing joy and fulfillment in the present moment. Nguyen’s approach is minimalist and practical, avoiding complex psychological jargon in favor of direct insights that can be applied immediately. It serves as a guide for anyone looking to quiet their mind and live a life driven by inspiration rather than fear.',
                'cover_image' => 'pdf_books/user_1/Don_t_Believe_Everything_You_Think_book_cover.jpg',
                'price' => 16,
            ],
            [
                'title' => 'Ego Is the Enemy',
                'author' => 'Ryan Holiday',
                'isbn' => '9781591847816',
                'publisher' => 'Portfolio',
                'year' => 2016,
                'pages' => 256,
                'file_size' => 1782579,
                'seller_id' => 1,
                'description' => 'In an era that glorifies social media, reality TV, and other forms of shameless self-promotion, the battle against ego is more important than ever. Ryan Holiday argues that ego is the internal opponent that thwarts our efforts at every stage of life: whether we are aspiring to greatness, experiencing success, or dealing with failure. Drawing on the wisdom of Stoic philosophy and historical examples ranging from George Marshall to Eleanor Roosevelt, Holiday shows how the greatest leaders achieved their goals by subduing their egos and focusing on the work itself. He breaks the book into three parts—Aspire, Success, and Failure—to show how ego manifests differently in each phase. The book serves as a practical meditation on humility and discipline, urging readers to be "humble in our aspirations, gracious in our success, and resilient in our failures." It is a vital read for anyone looking to build a career or life based on reality rather than delusion.',
                'cover_image' => 'pdf_books/user_1/Ego_Is_the_Enemy_book_cover.jpg',
                'price' => 25,
            ],
        ];

        foreach ($bookTemplates as $index => $template) {
            // Get the corresponding drive file or generate a fake one
            $driveFile = $sampleDriveFiles[$index % count($sampleDriveFiles)] ?? [
                'file_id' => $faker->uuid(),
                'shareable_link' => null
            ];

            PdfBook::create([
                'title'            => $template['title'],
                'author'           => $template['author'],
                'seller_id'        => $faker->randomElement($sellerIds),
                'isbn'             => $template['isbn'],
                'description'      => $template['description'],
                'publisher'        => $template['publisher'],
                'publication_year' => $template['year'],        
                'cover_image'      => $template['cover_image'],
                'price'            => $template['price'],
                'google_drive_file_id'         => $driveFile['file_id'], 
                'google_drive_shareable_link'  => $driveFile['shareable_link'],
                'file_size'        => $template['file_size'],
                'is_available'     => $faker->boolean(90),
                'total_pages'      => $template['pages'],
                'language'         => 'en',
            ]);
        }

        $this->command->info('PDF Books seeded successfully!');
    }
}
