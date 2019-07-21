<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SongRequest;

class QueueController extends Controller
{
    public function addToQueue(Request $request)
    {
        $request->validate([
            'original_url' => 'required|string',
        ]);

        $sRequest = new \App\SongRequest;
        $sRequest->original_url = $request->original_url;
        $sRequest->by = request()->ip();

        $det = $this->getVideoInfo($request->original_url);

        // \Log::debug($det);
        if ($det != null) {
            $sRequest->title = $det['title'];
            $sRequest->length = $det['length_seconds'];
        }

        $dnldUrl = $this->getDownloadUrl($request->original_url);

        $sRequest->save();

        return response()->json($sRequest);
    }

    public function getQueue()
    {
        $queue = \App\SongRequest::where('played', false)->limit(10)->get();

        $res = [];
        foreach ($queue as $q) {
            $myVote = \App\Votes::where('song_id', $q->id)->where('ip', request()->ip())->first();
            $q['myvote'] = $myVote ? $myVote->upvote ? 1 : -1 : 0;
            $q['upvote'] = \App\Votes::where('song_id', $q->id)->where('upvote', true)->count();
            $q['downvote'] = \App\Votes::where('song_id', $q->id)->where('upvote', false)->count();
            $q['own'] = request()->ip() == $q->by;
            $vote = \App\Votes::where('song_id', $q->id)->orderBy('updated_at', 'desc')->first();
            if ($vote) {
                $now = \Carbon\Carbon::now();
                $timeDiff = $now->diffInMinutes($vote->updated_at);
                \Log::debug($timeDiff.'b;a');
                if ($q->upvote < $q->downvote && $timeDiff != 0)
                    $q->delete();
            }
            else array_push($res, $q);
        }

        return response()->json($queue);
    }

    public function getPlayQueue()
    {
        $queue = \App\SongRequest::where('played', false)->limit(10)->get();

        $res = [];
        foreach ($queue as $q) {
            $myVote = \App\Votes::where('song_id', $q->id)->where('ip', request()->ip())->first();
            $q['myvote'] = $myVote ? $myVote->upvote ? 1 : -1 : 0;
            $q['upvote'] = \App\Votes::where('song_id', $q->id)->where('upvote', true)->count();
            $q['downvote'] = \App\Votes::where('song_id', $q->id)->where('upvote', false)->count();
            if ($q->upvote >= $q->downvote)
                array_push($res, $q);
        }

        return response()->json($res);
    }

    public function markPlayed(Request $request)
    {
        $request->validate([
            'id' => 'int|required'
        ]);
        // \Log::debug($request->id);
        $queue = \App\SongRequest::find($request->id);
        if ($queue) {
            $queue->played = true;
            $queue->playing = false;
            $queue->save();
        }

        return response()->json(['success' => true]);
    }

    public function markPlaying(Request $request)
    {
        $request->validate([
            'id' => 'int|required',
            'play' => 'boolean|required'
        ]);
        // \Log::debug($request->id);
        $queue = \App\SongRequest::find($request->id);
        if ($queue) {
            if ($request->play) {
                \App\SongRequest::where('playing', true)
                    ->update(['playing' => false]);
            }
            $queue->playing = $request->play;
            $queue->save();
        }

        return response()->json(['success' => true]);
    }

    public function changeVote(Request $request)
    {
        $request->validate([
            'id' => 'int|required',
            'increment' => 'boolean|required'
        ]);

        $req = \App\SongRequest::find($request->id);
        if ($req != null) {
            $vote = \App\Votes::where('song_id', $req->id)->where('ip', request()->ip())->first();
            if ($vote == null) {
                $vote = new \App\Votes;
                $vote->ip = request()->ip();
                $vote->song_id = $req->id;
                $vote->upvote = $request->increment;
                $vote->save();
            } else {
                if ($vote->upvote) {
                    if ($request->increment) $vote->delete();
                    else {
                        $vote->upvote = false;
                        $vote->save();
                    }
                } else {
                    if (!$request->increment) $vote->delete();
                    else {
                        $vote->upvote = true;
                        $vote->save();
                    }
                }
            }
        }

        return response()->json($vote);
    }

    public function deleteFromQueue(Request $request)
    {

        $request->validate([
            'id' => 'int|required',
        ]);

        $queue = \App\SongRequest::find($request->id);
        if ($queue) {
            $queue->delete();
        }

        return response()->json(['success' => true]);
    }

    private function getVideoInfo($url)
    {
        $id = substr($url, 32, 11);
        // \Log::debug($id);
        $content = file_get_contents("http://youtube.com/get_video_info?video_id=" . $id);
        parse_str($content, $ytarr);
        // \Log::debug($ytarr);
        return $ytarr;
    }

    public function getDownloadUrl($orgUrl)
    {
        $content = file_get_contents("http://michaelbelgium.me/ytconverter/convert.php?youtubelink=" . $orgUrl);
        $jsonD = json_decode($content);
        // \Log::debug($content);
    }
}
