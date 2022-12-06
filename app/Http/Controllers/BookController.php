<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookReviews;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{

    public function index()
    {
        $books = Book::with("authors","category","editorial")->get();
        //$books = Book::all(); Solo para ver los libros
        return [
            "error" => false,
            "message" => "Successfull",
            "data" => $books
        ];
    }

    public function store (Request $request){
        DB::beginTransaction();
        try{
            $existIbsn = Book::where('isbn', trim($request->isbn))->exists();
            if(!$existIbsn){
                $book = new Book();
                $book->isbn = trim($request->isbn);
                $book->title = $request->title;
                $book->description = $request->description;
                $book->category_id = $request->category_id;
                $book->editorial_id = $request->editorial_id;
                $book->publish_date = Carbon::now();
                $book->save();
                //$bookId->$book->id;
                foreach($request->authors as $item){
                    $book->authors()->attach($item);
                }
                return [
                    "status" => true,
                    "message" => "your book has benn created",
                    "data" => [
                        "book" => $book
                    ]
                ];
            }else{
                return [
                    "status" => false,
                    "message" => "the ISBN already exist",
                    "data" => []
                ];
            }
            DB::commit();
        }catch(Exception $e){
            return [
                "status" => true,
                "message" => "Wrong operation",
                "data" => []
            ];
            DB::rollBack();
        }
    }

    public function update(Request $request, $id){
        $response = $this->getResponse();
        try{
            $book = Book::find($id);
            if($book){
                $isbnOwner = Book::where("isbn", $request->isbn)->first();
                if(!$isbnOwner || $isbnOwner->id == $book->id){
                    $book->isbn = trim($request->isbn);
                    $book->title = $request->title;
                    $book->description = $request->description;
                    $book->category_id = $request->category_id;
                    $book->editorial_id = $request->editorial_id;
                    $book->publish_date = Carbon::now();
                    $book->update();
                    //Deleted
                    foreach($book->authors as $item){
                        $book->authors()->detach($item->id);
                    }
                    //Add
                    foreach($request->authors as $item){
                        $book->authors()->attach($item);
                    }
                    $book = Book::with('category', 'editorial', 'authors')->where("id" , $id)->get();
                    $response["error"] = false;
                    $response["message"] = "Your book has been update";
                    $response["data"] = $book;
                }else {
                    $response["message"] = "ISBN duplicated";
                }
            }else{
                $response["message"] = "Not found";
            }
            DB::commit();
        }catch(Exception $e){
            DB::rollBack();
        }

        return $response;
    }

    public function getById(Request $request, $id)
    {
        $book = Book::find($id);
        if($book){
            $books = Book::with("authors","category","editorial")->where("id", $request->id)->get();
            return [
                "error" => false,
                "message" => "Successfull",
                "data" => $books
            ];
        }else{
            return [
                "error" => true,
                "message" => "Book not found"
            ];
        }
    }

    public function delete($id)
    {
        $book = Book::find($id);
        if($book){
            foreach($book->authors as $item){
                $book->authors()->detach($item->$id);
            }
            $book->delete();
            return [
                "error" => false,
                "message" => "Successfull",
                "data" => $book->$id
            ];
        }else{
            return [
                "error" => true,
                "message" => "No hay libro"
            ];
        }
    }

    public function addBookReview(Request $request, $id){
        $userAuth = auth()->user();
        if (isset($userAuth->id)) {
            $book_review = new BookReviews();
            $book_review->comment = $request->comment;
            $book_review->edited = false;
            $book_review->user_id = $userAuth->id;
            $book_review->book_id = $id;
            $book_review->save();
            return $this->getResponse201('review', 'created', $book_review);
        }else{
            return $this->getResponse401();
        }
    }

    public function updateBookReview(Request $request, $idComment){
        $userAuth = auth()->user();
        if (isset($userAuth->id)) {
            $book_review = BookReviews::find($idComment);
            if($book_review){
                if($book_review->user_id == $userAuth->id){
                    $book_review->comment = $request->comment;
                    $book_review->edited = true;
                    $book_review->update();
                    return $this->getResponse201('review', 'updated', $book_review);
                }else{
                    return $this->getResponse403();
                }
            }else{
                return $this->getResponse404();
            }
        }else{
            return $this->getResponse401();
        }
    }
}
