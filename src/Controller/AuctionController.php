<?php
namespace App\Controller;

use App\Controller\AppController;

use Cake\Event\Event;
use Exception;
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;
use App\Controller\log;

class AuctionController extends AuctionBaseController {

    // デフォルトテーブルを使わない
    public $useTable = false;

    // 初期化処理
    public function initialize() {
        parent::initialize();
        $this->loadComponent('Paginator');
        // 必要なモデルをすべてロード
        $this->loadModel('Users');
        $this->loadModel('Biditems');
        $this->loadModel('Bidrequests');
        $this->loadModel('Bidinfo');
        $this->loadModel('Bidmessages');
        // ログインしているユーザー情報をauthuserに設定
        $this->set('authuser', $this->Auth->user());
        // レイアウトをauctionに変更
        $this->viewBuilder()->setLayout('auction');
    }

    // トップページ
    public function index() {
        // ページネーションでBiditemsを取得
        $auction = $this->paginate('Biditems', [
            'order' => ['endtime' => 'desc'],
            'limit' => 10]);
        $this->set(compact('auction'));
    }

    // 商品情報の表示
    public function view($id = null) {
        // $idのBiditemを取得
        $biditem = $this->Biditems->get($id, [
            'contain' => ['Users', 'Bidinfo', 'Bidinfo.Users']
        ]);
        // オークション終了時の処理
        if($biditem->endtime < new \DateTime('now') and $biditem->finished == 0) {
            // finishedを1に変更して保存
            $biditem->finished = 1;
            $this->Biditems->save($biditem);
            // Bidinfoを作成する
            $bidinfo = $this->Bidinfo->newEntity();
            // Bidinfoのbiditem_idに$idを設定
            $bidinfo->biditem_id = $id;
            // 最高金額のBidrequestを検索
            $bidrequest = $this->Bidrequests->find('all', [
                'conditions' => ['biditem_id' => $id],
                'contain' => ['Users'],
                'order' => ['price' => 'desc']])->first();
            // Bidrequestが得られた時の処理
            if(!empty($bidrequest)) {
                // Bidinfoの各種プロパティを設定して保存する
                $bidinfo->user_id = $bidrequest->user->id;
                $bidinfo->user = $bidrequest->user;
                $bidinfo->price = $bidrequest->price;
                $this->Bidinfo->save($bidinfo);
            }
            // Biditemのbidinfoに$bidinfoを設定
            $biditem->bidinfo = $bidinfo;
        }
        // Bidrequestsからbiditem_idが$idのものを取得
        $bidrequests = $this->Bidrequests->find('all', [
            'conditions' => ['biditem_id' => $id],
            'contain' => ['Users'],
            'order' => ['price' => 'desc']])->toArray();
        // オブジェクト類をテンプレート用に設定
        $this->set(compact('biditem', 'bidrequests'));
    }

    
    // 出品する処理
    public function add() {

        // Biditemインスタンスを用意
        $biditem = $this->Biditems->newEntity();

        // POST送信時の処理
        if($this->request->is('post')) {

            // 移動先フォルダを指定
            $dir = WWW_ROOT . 'goods_images' . DS;
            // ファイル名を取得
            $filename = $this->request->getData('goods_image.name');
            // ファイル名に現在日時を付与
            $new_filename = date('YmdHis') . $filename;
            // アップロード絶対パス
            $uploadfile = $dir . $new_filename;
            // 一時保存先を入れる
            $tmp_file = $this->request->getData('goods_image.tmp_name');
            // 拡張子を取り出す
            $ext = substr($filename, strrpos($filename, '.') + 1);

            // 画像ファイル情報を取得
            $file = new File($tmp_file);

            // DB保存用にファイル名を入れる
            $data = array(
                'user_id' => $this->request->getData('user_id'),
                'name' => $this->request->getData('name'),
                'goods_detail' => $this->request->getData('goods_detail'),
                'goods_image' => $new_filename,
                'finished' => $this->request->getData('finished'),
                'endtime' => $this->request->getData('endtime'),
            ); 

            // biditemにフォームの送信内容を反映
            $biditem = $this->Biditems->patchEntity($biditem, $data);
            // バリデーション
            if($biditem->errors()) {
                // 失敗時
                $this->Flash->error(__('失敗しました。もう一度入力下さい。'));
            } else {
                // 成功時
                // 画像ファイルが選択されているか確認
                if(empty($filename)) {
                    $this->Flash->error(__('画像ファイルを選択してください。'));
                // 拡張子確認 ----
                } elseif (
                    ($ext !== 'jpeg') &&
                    ($ext !== 'jpg') &&
                    ($ext !== 'gif') &&
                    ($ext !== 'png')) {
                        $this->Flash->error(__('拡張子がjpg,jpeg,png,gifのみ選択可能です'));
                    
                    // ファイル名の長さ確認 -----
                    } elseif (strlen($new_filename) > 100) {
                        $this->Flash->error(__('ファイル名が長すぎます'));

                    // ファイルサイズ確認 -----
                    } elseif ($file->size() >= 1000000) {
                        $this->Flash->error(__('ファイルサイズが超過しています（MaxSize:1M）'));

                    // mimetype確認 -----
                    } elseif(
                        ($file->mime() !== 'image/jpeg') &&
                        ($file->mime() !== 'image/gif') &&
                        ($file->mime() !== 'image/png')) {
                        $this->Flash->error(__('jpeg,png,gif形式のファイルを選択して下さい'));

                    } else {
                        // 一時保存ファイルの場所を移動させる
                        if(move_uploaded_file($tmp_file, $uploadfile)) {

                            if($this->Biditems->save($biditem)) {
                            // 成功時
                            $this->Flash->success(__('保存しました。'));
                            // indexへ移動
                            return $this->redirect(['action' => 'index']);
                            }
                        }
                        $this->Flash->error(__('保存に失敗しました。もう一度入力下さい。'));
                    }
            }
        }
        // 値を補間
        $this->set(compact('biditem'));
    }

    // 入札の処理
    public function bid($biditem_id = null) {
        // 入札用のBidrequestインスタンスを用意
        $bidrequest = $this->Bidrequests->newEntity();
        // $bidrequestにbiditem_idとuser_idを設定
        $bidrequest->biditem_id = $biditem_id;
        $bidrequest->user_id = $this->Auth->user('id');
        // POST送信時の処理
        if($this->request->is('post')) {
            // $bidrequestに送信フォームの内容を反映する
            $bidrequest = $this->Bidrequests->patchEntity($bidrequest, $this->request->getData());
            // Bidrequestを保存
            if($this->Bidrequests->save($bidrequest)) {
                // 成功時のメッセージ
                $this->Flash->success(__('入札を送信しました。'));
                // トップページにリダイレクト
                return $this->redirect(['action' => 'view', $biditem_id]);
                }
                // 失敗時のメッセージ
                $this->Flash->error(__('入札に失敗しました。もう一度入力下さい。'));
            }
            // $biditem_idの$biditemを取得する
            $biditem = $this->Biditems->get($biditem_id);
            $this->set(compact('bidrequest', 'biditem'));
        }

        // 落札者とのメッセージ
        public function msg($bidinfo_id = null) {
            // Bidmessageを新たに用意
            $bidmsg = $this->Bidmessages->newEntity();
            // POST送信時の処理
            if($this->request->is('post')) {
                // 送信されたフォームで$bidmsgを更新
                $bidmsg = $this->Bidmessages->patchEntity($bidmsg, $this->request->getData());
                // Bidmessageを保存
                if($this->Bidmessages->save($bidmsg)) {
                    $this->Flash->success(__('保存しました。'));
                } else {
                    $this->Flash->error(__('保存に失敗しました。もう一度入力下さい。'));
                }
            }
            try { // $bidinfo_idからBidinfoを取得する
                $bidinfo = $this->Bidinfo->get($bidinfo_id, ['contain' => ['Biditems']]);
            } catch(Exception $e) {
                $bidinfo = null;
            }
            // Bidmessageをbidinfo_idとuser_idで検索
            $bidmsgs = $this->Bidmessages->find('all', [
                'conditions' => ['bidinfo_id' => $bidinfo_id],
                'contain' => ['Users'],
                'order' => ['created' => 'desc']]);
            $this->set(compact('bidmsgs', 'bidinfo', 'bidmsg'));
        }

        // 落札情報の表示
        public function home() {
            // 自分が落札したBidinfoをページネーションで取得
            $bidinfo = $this->paginate('Bidinfo', [
                'conditions' => ['Bidinfo.user_id' => $this->Auth->user('id')],
                'contain' => ['Users', 'Biditems'],
                'order' => ['created' => 'desc'],
                'limit' => 10])->toArray();
            $this->set(compact('bidinfo'));
        }

        // 出品情報の表示
        public function home2() {
            // 自分が出品したBiditemをページネーションで取得
            $biditems = $this->paginate('Biditems', [
                'conditions' => ['Biditems.user_id' => $this->Auth->user('id')],
                'contain' => ['Users', 'Bidinfo'],
                'order' => ['created' => 'desc'],
                'limit' => 10])->toArray();
            $this->set(compact('biditems'));
        }
}
